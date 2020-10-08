<?php
use PHPMailer\PHPMailer\PHPMailer;
require '../vendor/autoload.php';
if (!(isset($_POST['txt_element'])&&isset($_POST['email'])))
{
    echo 'Bitte alle Pflichtfelder ausfüllen';
    die(0);
}

// Datenbank verbinden
$mysqli = mysqli_connect("localhost", "support_db", "support_db", "support");
if (mysqli_connect_errno()) {
    echo "Fehler beim Speichern";
}
else {
    // Settings schreiben
    $other=(($_POST['other']==='true') ? 1 : 0);
    $person=(($_POST['person']==='true') ? 1 : 0);
    $copoyright=(($_POST['copyright']==='true') ? 1 : 0);
    $stmt = $mysqli->prepare("INSERT INTO `abuse` (`txt_element`, `firstName`, `lastName`,`email`, `person`, `copyright`,`other`,`ip`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiis",
        $_POST['txt_element'],
        $_POST['firstName'],
        $_POST['lastName'],
        $_POST['email'],
        $person,
        $copoyright,
        $other,
        $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    if($stmt->affected_rows < 1) {
        echo "Fehler beim Speichern";
        die(0);
    }
    $stmt->close();
    $id = $mysqli->insert_id;
    echo "Ihre Anfrage wurde unter der ID: $id gespeichert!";

    $mysqli->close();
    $reason = '';
    if ($_POST['person']==='true') {
        $reason = 'Personenbezogene Daten';
    }
    if ($_POST['copyright']==='true') {
        $reason = 'Problem in Bezug auf geistiges Eigentum';
    }
    if ($_POST['other']==='true') {
        $reason = 'Anderes rechtliches Problem';
    }

    $empfaenger = 'tourist@lra.landkreis-cham.de';
    $email = new PHPMailer();
    $email->CharSet = 'utf-8';
    $email->isHTML(true);
    $email->SetFrom('info@bayerischer-wald.org', 'Bayerischer Wald org'); //Name is optional
    $email->Subject   = 'Neue Meldung bezüglich Entfernung von Inhalten';
    $email->Body      = 'Hallo, <br/>es ist eine neue Meldung bezüglich dem Entfernen von Inhalten eingetroffen <br/>'.
        '<b>Grund der Meldung: </b>'.$reason.'</br>'.
        '<b>Inhalt der Meldung</b>'.
        '<p>'.$_POST['txt_element'].'</p>'.
        '<b>Vorname: </b>'.$_POST['firstName'].'<b> Nachname: </b>'.$_POST['lastName'].'<br/>'.
        '<b>eMail: </b>'.$_POST['email'];
    $email->AddAddress( $empfaenger );
    $email->Send();
}