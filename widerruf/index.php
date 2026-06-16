<?php
declare(strict_types=1);

/**
 * Elektronische Widerrufsfunktion nach § 356a BGB (ab 19.06.2026).
 *
 * Eigenständige Seite. Von der Hauptseite mit einem gut sichtbaren,
 * hervorgehobenen Button verlinken, der mit "Vertrag widerrufen"
 * (oder gleichwertig) beschriftet ist – dieser Link IST die Stufe 1
 * im Sinne des Gesetzes.
 *
 * Ablauf hier:
 *   Stufe A  ->  Formular mit den Angaben
 *   Stufe B  ->  Zusammenfassung + Button "Widerruf bestätigen"
 *   Ergebnis ->  Eintrag in MySQL, Eingangsbestätigung per E-Mail
 *
 * Kein sichtbares Captcha (das wäre nach § 356a BGB unzulässig, weil es
 * den Widerruf erschwert). Spamschutz läuft unsichtbar im Hintergrund:
 * Honeypot-Feld, Zeitfalle und CSRF-Token.
 */

session_start();

$config = require __DIR__ . '/includes/config.php';

// --------------------------------------------------------------------------
// Hilfsfunktionen
// --------------------------------------------------------------------------

function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(?string $t): bool
{
    return is_string($t) && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $t);
}

/** Liest und trimmt ein POST-Feld. */
function post(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

/**
 * Validiert die eingegebenen Daten.
 * Pflicht: Name, E-Mail, Vertrags-/Bestellnummer.
 * Optional: Telefon, Grund (Grund darf NIE Pflicht sein).
 *
 * @return array{0: array<string,string>, 1: array<string,string>}  [werte, fehler]
 */
function validate(): array
{
    $werte = [
        'name'    => post('name'),
        'email'   => post('email'),
        'telefon' => post('telefon'),
        'vertrag' => post('vertrag'),
        'grund'   => post('grund'),
    ];
    $fehler = [];

    if ($werte['name'] === '') {
        $fehler['name'] = 'Bitte geben Sie Ihren Namen an.';
    }
    if ($werte['email'] === '' || !filter_var($werte['email'], FILTER_VALIDATE_EMAIL)) {
        $fehler['email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
    }
    if ($werte['vertrag'] === '') {
        $fehler['vertrag'] = 'Bitte geben Sie die Vertrags- bzw. Bestellnummer an.';
    }

    return [$werte, $fehler];
}

/**
 * Unsichtbarer Spamschutz. Gibt true zurück, wenn die Anfrage
 * mit hoher Wahrscheinlichkeit von einem Bot stammt.
 */
function is_spam(array $config): bool
{
    // 1) Honeypot: für Menschen unsichtbares Feld. Ist es ausgefüllt -> Bot.
    if (post('website') !== '') {
        return true;
    }

    // 2) Zeitfalle: zu schnell oder Formular zu alt.
    $opened = (int)($_SESSION['form_opened'] ?? 0);
    if ($opened <= 0) {
        return true; // Formular wurde nie regulär geöffnet.
    }
    $elapsed = time() - $opened;
    if ($elapsed < (int)$config['spam']['min_seconds']) {
        return true;
    }
    if ($elapsed > (int)$config['spam']['max_seconds']) {
        return true;
    }

    return false;
}

function db(array $config): PDO
{
    $d = $config['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $d['host'], $d['port'], $d['name'], $d['charset']
    );
    return new PDO($dsn, $d['user'], $d['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

function make_reference(): string
{
    return 'WR-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Speichert den Widerruf. Gibt [referenz, zeitpunkt] zurück.
 *
 * @return array{0:string,1:DateTimeImmutable}
 */
function store(PDO $pdo, array $werte): array
{
    $now    = new DateTimeImmutable('now');
    $ipBin  = isset($_SERVER['REMOTE_ADDR'])
        ? (inet_pton($_SERVER['REMOTE_ADDR']) ?: null)
        : null;
    $ua     = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    $sql = 'INSERT INTO widerrufe
              (referenz, created_at, name, email, telefon, vertrag, grund, ip, user_agent)
            VALUES
              (:referenz, :created_at, :name, :email, :telefon, :vertrag, :grund, :ip, :ua)';

    // Bei (sehr unwahrscheinlicher) Referenz-Kollision einmal neu würfeln.
    for ($try = 0; $try < 3; $try++) {
        $referenz = make_reference();
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':referenz',   $referenz);
            $stmt->bindValue(':created_at', $now->format('Y-m-d H:i:s'));
            $stmt->bindValue(':name',       $werte['name']);
            $stmt->bindValue(':email',      $werte['email']);
            $stmt->bindValue(':telefon',    $werte['telefon'] !== '' ? $werte['telefon'] : null);
            $stmt->bindValue(':vertrag',    $werte['vertrag']);
            $stmt->bindValue(':grund',      $werte['grund'] !== '' ? $werte['grund'] : null);
            $stmt->bindValue(':ip',         $ipBin, $ipBin === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
            $stmt->bindValue(':ua',         $ua !== '' ? $ua : null);
            $stmt->execute();
            return [$referenz, $now];
        } catch (PDOException $ex) {
            // 23000 = Integrity constraint (z. B. doppelte Referenz)
            if ($ex->getCode() !== '23000' || $try === 2) {
                throw $ex;
            }
        }
    }
    throw new RuntimeException('Speichern fehlgeschlagen.');
}

// --------------------------------------------------------------------------
// Routing / Zustandsmaschine
// --------------------------------------------------------------------------

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $method === 'POST' ? post('action') : '';

$view   = 'form';          // form | confirm | success | error
$werte  = ['name' => '', 'email' => '', 'telefon' => '', 'vertrag' => '', 'grund' => ''];
$fehler = [];
$ergebnis = null;          // [referenz, DateTimeImmutable]
$fehlertext = '';

if ($action === 'zurueck') {
    // Von der Bestätigung zurück zum Formular -> Werte erhalten.
    $werte = [
        'name'    => post('name'),
        'email'   => post('email'),
        'telefon' => post('telefon'),
        'vertrag' => post('vertrag'),
        'grund'   => post('grund'),
    ];
    $view = 'form';

} elseif ($action === 'pruefen') {
    // Stufe A abgesendet -> validieren -> Zusammenfassung zeigen.
    if (is_spam($config)) {
        // Stillschweigend wie Erfolg behandeln, um Bots keine Rückmeldung zu geben.
        $view = 'success_silent';
    } else {
        [$werte, $fehler] = validate();
        $view = $fehler === [] ? 'confirm' : 'form';
    }

} elseif ($action === 'bestaetigen') {
    // Stufe B abgesendet -> endgültig verarbeiten.
    if (!csrf_valid(post('csrf')) || is_spam($config)) {
        $view = 'success_silent';
    } else {
        [$werte, $fehler] = validate();
        if ($fehler !== []) {
            $view = 'form'; // Sollte bei korrektem Durchlauf nicht passieren.
        } else {
            try {
                $pdo = db($config);
                $ergebnis = store($pdo, $werte);
                send_confirmation($config, $werte, $ergebnis[0], $ergebnis[1]);
                // Token entwerten -> kein versehentliches Doppel-Absenden.
                unset($_SESSION['csrf'], $_SESSION['form_opened']);
                $view = 'success';
            } catch (Throwable $t) {
                error_log('[Widerruf] ' . $t->getMessage());
                $fehlertext = 'Beim Verarbeiten ist ein technischer Fehler aufgetreten. '
                    . 'Bitte versuchen Sie es später erneut oder kontaktieren Sie uns direkt.';
                $view = 'error';
            }
        }
    }
}

// Beim ersten Aufruf bzw. neuem Formular: Zeitfalle und Token setzen.
if ($view === 'form' && $action === '') {
    $_SESSION['form_opened'] = time();
}
csrf_token();

// --------------------------------------------------------------------------
// Mailversand
// --------------------------------------------------------------------------

/**
 * Sendet die Eingangsbestätigung an den Verbraucher (dauerhafter
 * Datenträger i. S. d. § 356a BGB) und optional eine Kopie an den Anbieter.
 *
 * Hinweis: Für zuverlässige Zustellung statt mail() besser PHPMailer + SMTP
 * verwenden. Beispiel am Dateiende auskommentiert.
 */
function send_confirmation(array $config, array $werte, string $referenz, DateTimeImmutable $zeit): void
{
    $firma   = $config['company']['name'];
    $zeitStr = $zeit->format('d.m.Y, H:i:s');

    $datenBlock =
        "Referenz:               {$referenz}\n" .
        "Eingang am:             {$zeitStr} Uhr\n" .
        "Name:                   {$werte['name']}\n" .
        "E-Mail:                 {$werte['email']}\n" .
        ($werte['telefon'] !== '' ? "Telefon:                {$werte['telefon']}\n" : '') .
        "Vertrag/Bestellung:     {$werte['vertrag']}\n" .
        ($werte['grund'] !== ''   ? "Angegebener Grund:      {$werte['grund']}\n" : '');

    // ---- Mail an den Verbraucher (Eingangsbestätigung) ----
    $betreff = "Eingangsbestätigung Ihres Widerrufs – {$referenz}";
    $text =
        "Sehr geehrte/r {$werte['name']},\n\n" .
        "wir bestätigen den Eingang Ihres Widerrufs. Sie haben den nachstehend\n" .
        "bezeichneten Vertrag mit der {$firma} wirksam widerrufen.\n\n" .
        $datenBlock . "\n" .
        "Diese E-Mail dient als Eingangsbestätigung auf einem dauerhaften\n" .
        "Datenträger im Sinne des § 356a BGB. Bitte bewahren Sie sie auf.\n\n" .
        "Mit freundlichen Grüßen\n{$firma}\n{$config['company']['address']}\n";

    send_mail($config, $werte['email'], $betreff, $text);

    // ---- Kopie an den Anbieter (zur internen Bearbeitung) ----
    $opEmail = trim((string)($config['mail']['notify_operator'] ?? ''));
    if ($opEmail !== '') {
        $opBetreff = "Neuer Widerruf eingegangen – {$referenz}";
        $opText    = "Es ist ein neuer Widerruf eingegangen:\n\n" . $datenBlock;
        send_mail($config, $opEmail, $opBetreff, $opText, $werte['email']);
    }
}

function send_mail(array $config, string $to, string $betreff, string $text, ?string $replyTo = null): void
{
    $fromName  = $config['mail']['from_name'];
    $fromEmail = $config['mail']['from_email'];

    $encSubject = '=?UTF-8?B?' . base64_encode($betreff) . '?=';
    $encFrom    = '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>';

    $headers   = [];
    $headers[] = 'From: ' . $encFrom;
    $headers[] = 'Reply-To: ' . ($replyTo ?: $fromEmail);
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';
    $headers[] = 'MIME-Version: 1.0';

    @mail($to, $encSubject, $text, implode("\r\n", $headers), '-f' . $fromEmail);
}

// --------------------------------------------------------------------------
// Ausgabe
// --------------------------------------------------------------------------
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Vertrag widerrufen</title>
    <link media="all" rel="stylesheet" href="./style.css">
   
</head>
<body>
<div class="wrap">

    <header class="brand">
        <!-- LOGO: Platzhalter ersetzen (siehe CSS-Kommentar oben) -->
        <div class="logo-placeholder">Logo</div>
    </header>

    <div class="card">
<?php if ($view === 'form'): ?>

        <h1>Vertrag widerrufen</h1>
        <p class="lead">
            Mit diesem Formular können Sie einen online geschlossenen Vertrag
            widerrufen. Im nächsten Schritt prüfen Sie Ihre Angaben und
            bestätigen den Widerruf.
        </p>

        <form method="post" action="" autocomplete="on" novalidate>
            <input type="hidden" name="action" value="pruefen">

            <div class="<?= isset($fehler['name']) ? 'field-err' : '' ?>">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= e($werte['name']) ?>" required>
                <?php if (isset($fehler['name'])): ?><div class="err"><?= e($fehler['name']) ?></div><?php endif; ?>
            </div>

            <div class="<?= isset($fehler['email']) ? 'field-err' : '' ?>">
                <label for="email">E-Mail-Adresse <span class="opt">(für die Eingangsbestätigung)</span></label>
                <input type="email" id="email" name="email" value="<?= e($werte['email']) ?>" required>
                <?php if (isset($fehler['email'])): ?><div class="err"><?= e($fehler['email']) ?></div><?php endif; ?>
            </div>

            <div class="<?= isset($fehler['vertrag']) ? 'field-err' : '' ?>">
                <label for="vertrag">Vertrags- bzw. Bestellnummer</label>
                <input type="text" id="vertrag" name="vertrag" value="<?= e($werte['vertrag']) ?>" required>
                <?php if (isset($fehler['vertrag'])): ?><div class="err"><?= e($fehler['vertrag']) ?></div><?php endif; ?>
            </div>

            <div>
                <label for="telefon">Telefon <span class="opt">(optional)</span></label>
                <input type="text" id="telefon" name="telefon" value="<?= e($werte['telefon']) ?>">
            </div>

            <div>
                <label for="grund">Grund des Widerrufs <span class="opt">(optional, keine Pflichtangabe)</span></label>
                <textarea id="grund" name="grund"><?= e($werte['grund']) ?></textarea>
            </div>

            <!-- Honeypot: bitte nicht ausfüllen (für Menschen unsichtbar) -->
            <div class="hp" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="actions">
                <button type="submit" class="btn">Weiter zur Bestätigung</button>
            </div>
        </form>

<?php elseif ($view === 'confirm'): ?>

        <h1>Widerruf bestätigen</h1>
        <p class="lead">
            Bitte prüfen Sie Ihre Angaben. Mit Klick auf
            „Widerruf bestätigen“ erklären Sie den Widerruf verbindlich.
        </p>

        <table class="summary">
            <tr><th>Name</th><td><?= e($werte['name']) ?></td></tr>
            <tr><th>E-Mail</th><td><?= e($werte['email']) ?></td></tr>
            <tr><th>Vertrag/Bestellung</th><td><?= e($werte['vertrag']) ?></td></tr>
            <?php if ($werte['telefon'] !== ''): ?>
            <tr><th>Telefon</th><td><?= e($werte['telefon']) ?></td></tr>
            <?php endif; ?>
            <?php if ($werte['grund'] !== ''): ?>
            <tr><th>Grund</th><td><?= nl2br(e($werte['grund'])) ?></td></tr>
            <?php endif; ?>
        </table>

        <form method="post" action="">
            <input type="hidden" name="csrf"    value="<?= e($token) ?>">
            <input type="hidden" name="name"    value="<?= e($werte['name']) ?>">
            <input type="hidden" name="email"   value="<?= e($werte['email']) ?>">
            <input type="hidden" name="telefon" value="<?= e($werte['telefon']) ?>">
            <input type="hidden" name="vertrag" value="<?= e($werte['vertrag']) ?>">
            <input type="hidden" name="grund"   value="<?= e($werte['grund']) ?>">

            <div class="actions">
                <button type="submit" class="btn" name="action" value="bestaetigen">Widerruf bestätigen</button>
                <button type="submit" class="btn secondary" name="action" value="zurueck">Angaben ändern</button>
            </div>
        </form>

<?php elseif ($view === 'success'): ?>

        <h1><span class="ok-icon">✓</span> Widerruf erfolgreich</h1>
        <p class="lead">
            Ihr Widerruf ist bei uns eingegangen. Eine Eingangsbestätigung
            wurde an <strong><?= e($werte['email']) ?></strong> gesendet.
        </p>
        <table class="summary">
            <tr><th>Referenz</th><td><?= e($ergebnis[0]) ?></td></tr>
            <tr><th>Eingang am</th><td><?= e($ergebnis[1]->format('d.m.Y, H:i:s')) ?> Uhr</td></tr>
        </table>
        <p style="margin-top:20px;">Sie können dieses Fenster nun schließen.</p>

<?php elseif ($view === 'success_silent'): ?>

        <h1><span class="ok-icon">✓</span> Widerruf erfolgreich</h1>
        <p class="lead">Ihr Widerruf wurde verarbeitet.</p>

<?php else: /* error */ ?>

        <h1>Es ist ein Fehler aufgetreten</h1>
        <p class="lead"><?= e($fehlertext) ?></p>
        <div class="actions">
            <a href="" class="btn secondary">Zurück zum Formular</a>
        </div>

<?php endif; ?>
    </div>

    <footer>
        Widerrufsfunktion gemäß § 356a BGB
    </footer>
</div>
</body>
</html>
