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
        'pass'    => 'BITTE_AENDERN',
        'charset' => 'utf8mb4',
    ],

    // ---- Anbieterdaten (für E-Mail-Texte) ------------------------------
    'company' => [
        'name'    => 'Musterfirma GmbH',
        'address' => 'Musterstraße 1, 93413 Cham',
        'email'   => 'info@example.com',   // Absender der Bestätigungsmail
    ],

    // ---- Mailversand ---------------------------------------------------
    // Hinweis: mail() funktioniert nur, wenn der Server korrekt zustellt
    // (SPF/DKIM!). Für zuverlässige Zustellung besser PHPMailer + SMTP –
    // siehe Kommentar in mailer.php.
    'mail' => [
        'from_name'        => 'Musterfirma GmbH',
        'from_email'       => 'no-reply@example.com',
        // Kopie an den Anbieter, damit der eingehende Widerruf intern
        // bearbeitet werden kann. Leer lassen, wenn nicht gewünscht.
        'notify_operator'  => 'widerruf@example.com',
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
