<?php
declare(strict_types=1);

/**
 * Zentrale Konfiguration.
 * Diese Datei NICHT ins öffentliche Web-Root legen bzw. per Webserver-Regel
 * vor direktem Abruf schützen (sie enthält Zugangsdaten).
 */

return [

    // ---- Datenbank (MySQL/MariaDB) -------------------------------------
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'widerruf',
        'user'    => 'widerruf_user',
        'pass'    => 'H8u_dcN~gZmtxh07',
        'charset' => 'utf8mb4',
    ],

    // ---- Anbieterdaten (für E-Mail-Texte) ------------------------------
    'company' => [
        'name'    => 'Tourist-Information Naturpark Oberer Bayerischer Wald',
        'address' => 'Rachelstraße 6, 93413 Cham',
        'email'   => 'info@bayerischer-wald.org',   // Absender der Bestätigungsmail
    ],

    // ---- Mailversand ---------------------------------------------------
    // Hinweis: mail() funktioniert nur, wenn der Server korrekt zustellt
    // (SPF/DKIM!). Für zuverlässige Zustellung besser PHPMailer + SMTP –
    // siehe Kommentar in mailer.php.
    'mail' => [
        'from_name'        => 'Tourist-Information Naturpark Oberer Bayerischer Wald',
        'from_email'       => 'no-reply@bayerischer-wald.org',
        // Kopie an den Anbieter, damit der eingehende Widerruf intern
        // bearbeitet werden kann. Leer lassen, wenn nicht gewünscht.
        'notify_operator'  => 'info@bayerischer-wald.org',
    ],

    // ---- Spamschutz (alles serverseitig, kein sichtbares Captcha) ------
    'spam' => [
        // Minimale Ausfüllzeit in Sekunden. Wer schneller absendet, ist
        // mit hoher Wahrscheinlichkeit ein Bot. Bewusst niedrig (3 s),
        // um echte, schnelle Nutzer nicht auszusperren.
        'min_seconds' => 3,
        // Maximale Gültigkeit eines geöffneten Formulars (Sekunden).
        'max_seconds' => 3600,
    ],
];
