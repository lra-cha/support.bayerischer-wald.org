<?php

if (!(isset($_POST['txt_element'])&&isset($_POST['email'])))
{
    echo 'Bitte alle Pflichtfelder ausfüllen';
    die(0);
}

// Datenbank verbinden
$mysqli = mysqli_connect("localhost", "di_check", "di_check", "di_check");
if (mysqli_connect_errno()) {
    echo "Fehler beim Speichern";
}
else {
    // Settings schreiben
    $stmt = $mysqli->prepare("INSERT INTO `settings` (`txt_element`, `firstName`, `lastName`,`email`, `person`, `copyright`,`other`,`ip`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiis", $_POST['txt_element'], $_POST['firstName'], $_POST['lastName'], $_POST['email'], ($_POST['person']?1:0), ($_POST['copyright']?1:0), ($_POST['other']?1:0), $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    if($stmt->affected_rows < 1) {
        echo "Fehler beim Speichern";
        die(0);
    }
    $stmt->close();
    $id = $mysqli->insert_id;
    echo "Ihre Anfrage wurde unter der ID: $id gespeichert!";

    $mysqli->close();
}