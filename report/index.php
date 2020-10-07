<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="/favicon.ico">
    <meta name="robots" content="index, follow">
    <title>Support</title>
    <link href="../libs/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="text-center">
<div class="container shadow p-3 bg-white  rounded-0">
    <div class="py-5 text-center">
        <img class="d-block mx-auto mb-4" src="../img/logo.svg" alt="" width="157" height="142">
        <h2>Entfernen von Inhalten</h2>
        <p class="lead">Auf dieser Seite haben Sie die Möglichkeit Inhalte zu melden, die Sie gemäß geltendem Recht aus unseren Seiten entfernen lassen möchten. Um Ihre Anfrage umfassend bearbeiten zu können möchten wir Sie bitten vollständige und ausführliche Angaben zum Sachverhalt zu machen.</p>
    </div>

    <div class="row">

        <div class="col-12 text-left">
            <h4 class="mb-3  text-center">Meldung</h4>
            <form >
                    <div class="form-group text-left">
                        <label for="txt_element">Welcher Eintrag soll entfernt werden?</label>
                        <textarea class="form-control" id="txt_element" rows="3"></textarea>
                        <small id="txt_elementHelpBlock" class="form-text text-muted">
                            Bitte genau Beschreibung (Link zum Eintrag, Terminal, Titel des Eintrags)
                        </small>
                    </div>


                <div class="mb-3">
                    <div class="d-block my-3 text-left">
                        <div class="custom-control custom-radio">
                            <input id="person17" name="reason" type="radio" class="custom-control-input">
                            <label class="custom-control-label" for="person17"><b>Personenbezogene Daten:</b> Recht auf Löschung, Art. 17 DSGVO</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input id="person16" name="reason" type="radio" class="custom-control-input">
                            <label class="custom-control-label" for="person16"><b>Personenbezogene Daten:</b> Recht auf Berichtigung, Art. 16 DSGVO</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input id="person18" name="reason" type="radio" class="custom-control-input" >
                            <label class="custom-control-label" for="person18"><b>Personenbezogene Daten:</b> Recht auf Einschränkung der Verarbeitung, Art. 18 DSGVO</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input id="copyright" name="reason" type="radio" class="custom-control-input">
                            <label class="custom-control-label" for="copyright"><b>Problem in Bezug auf geistiges Eigentum:</b> Verletzung oder Umgehung des Urheberrechts</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input id="other" name="reason" type="radio" class="custom-control-input">
                            <label class="custom-control-label" for="other"><b>Anderes rechtliches Problem:</b> Inhalte aus einem anderen rechtlichen Grund melden</label>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName">Vorname</label>
                        <input type="text" class="form-control" id="firstName" placeholder="" value="" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName">Nachname</label>
                        <input type="text" class="form-control" id="lastName" placeholder="" value="">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="name@mail.com">
                </div>

                <button class="btn btn-primary btn-lg btn-block" type="submit">Medlung abschicken</button>
            </form>
        </div>
    </div>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">Copyright © 1999 – 2020 | Landratsamt Cham | Rachelstraße 6 | 93413 Cham | Deutschland – Alle Rechte vorbehalten</p>
            <ul class="list-inline">
                <li class="list-inline-item"><a href="https://www.bayerischer-wald.org/impressum/">Impressum</a></li>
                <li class="list-inline-item"><a href="https://www.bayerischer-wald.org/datenschutz/">Datenschutzerklärung</a></li>
            </ul>
        </footer>

    </div>
    <script src="../libs/js/jquery-3.5.1.min.js"></script>
    <script src="../libs/js/bootstrap.bundle.js"></script>
    <script>

        // nach pageload ausführen
        $(function() {
            // ajaxEndpoint festelegen
            var ajaxUrl ='/ajax/';




        // Check Abfrage ausführen
        $('#checkbtn').on('click', function (e) {
            e.preventDefault();
            // alle Markierungen entfernen
            $('.form-group').removeClass('notvalid');
            // erst mal von "richtig ausgefüllt" ausgehen
            var invalid = false;
            var dz=$('.zipSelect').select2('data')
            var dc=$('.citySelect').select2('data')
            var ds=$('.streetSelect').select2('data')
            var dh=$('.houseSelect').select2('data')
            // wenn keine Werte gewählt das Formular auf infavalid setzen und die betreffenden Felder markieren
            if (typeof dz[0] === 'undefined') {$('.grp-zipSelect').addClass('notvalid');invalid=true;};
            if (typeof dc[0] === 'undefined') {$('.grp-citySelect').addClass('notvalid');invalid=true;};
            if (typeof ds[0] === 'undefined') {$('.grp-streetSelect').addClass('notvalid');invalid=true;};
            if (typeof dh[0] === 'undefined') {$('.grp-houseSelect').addClass('notvalid');invalid=true;};
            // wenn nicht alles passt abbrechen
            if(invalid) return;

            // Ab hier sind zumindest alle Felder befüllt
            // AJAX Abfrage ausführen um Einträge zu prüfen
            $.ajax({
                url: "/ajax/",
                cache: false,
                data: {
                    mode: "validate",
                    zip: dz[0].text,
                    city: dc[0].text,
                    street: ds[0].text,
                    house: dh[0].text
                    }
                })
                .done(function( resp ) {
                    console.log(resp);

                    if(resp.status==='wait') // wenn Status "wait" dann ist das abfragelimit erreicht
                    {
                        // Karte ausblenden
                        hideMap();
                        // und Warnung ausgeben
                        $('.warningbox').show();
                        $('#waitcount').text(resp.txtMaxRequestsTime-resp.age)
                        $('.alertbox').removeClass('alert-success').addClass('alert-warning');
                        // Timer starten der die verbleibenden Sekunden angibt
                        if (window.checktimer === undefined) window.checktimer = setInterval(window.handleTimerChange, 1000);
                    }
                    else { // falls das limit nicht erreicht ist Ergebnis anzeigen
                        // koordinaten aus dem Response übernehmen
                        var coords = resp.result[0]._source.location;
                        // warnung ausblenden und Kartenkontainer anzeigen
                        $('.warningbox').hide();
                        $('#mapid').show();
                        // falls leaflet noch nicht initialisiert wurde ein mal initialisieren und Hintergrundkarte drauf legen
                        if (window.checkmap === undefined)
                        {
                            window.checkmap = L.map('mapid');
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(window.checkmap);
                        }
                        // Karte auf die richtige Koordinate schieben
                        window.checkmap.setView([coords.lat, coords.lon], 17);

                        // falls noch nie ein Marker erstellt wurde diesen einmalig initialisieren
                        if (window.checkmarker === undefined) window.checkmarker = L.marker([coords.lat, coords.lon]).addTo(window.checkmap).bindPopup('');

                        // Marker auf die richtige Position setzen
                        window.checkmarker.setLatLng([coords.lat, coords.lon]);

                        // Inhalt des Markers anpassen
                        window.checkmarker.setPopupContent((resp.result[0]._source.avail?'<?php echo trim(json_encode(SETTINGS['txtFound']),'"');?>':'<?php echo trim(json_encode(SETTINGS['txtnotFound']),'"');?>'));

                        // Popup öffnen
                        window.checkmarker.openPopup();
                    }
                });
            });
        });
    </script>
</body>
</html>