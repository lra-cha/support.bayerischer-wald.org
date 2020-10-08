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
                        <textarea class="form-control form_val" id="txt_element" rows="3"></textarea>
                        <small id="txt_elementHelpBlock" class="form-text text-muted">
                            Bitte genau Beschreibung (Link zum Eintrag, Terminal, Titel des Eintrags)
                        </small>
                    </div>

                <h5 class="mb-3">Begründung</h5>
                <div class="mb-3">
                    <div class="d-block my-3 text-left">
                        <div class="custom-control custom-radio">
                            <input id="person" name="reason" type="radio" class="custom-control-input form_check_val">
                            <label class="custom-control-label" for="person"><b>Personenbezogene Daten:</b> Recht auf Löschung oder Berichtigung</label>
                        </div>
                         <div class="custom-control custom-radio">
                            <input id="copyright" name="reason" type="radio" class="custom-control-input form_check_val">
                            <label class="custom-control-label" for="copyright"><b>Problem in Bezug auf geistiges Eigentum:</b> Verletzung oder Umgehung des Urheberrechts</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input id="other" name="reason" type="radio" class="custom-control-input form_check_val">
                            <label class="custom-control-label" for="other"><b>Anderes rechtliches Problem:</b> Inhalte aus einem anderen rechtlichen Grund melden</label>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Kontaktdaten</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName">Vorname</label>
                        <input type="text" class="form-control form_val" id="firstName" placeholder="" value="" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName">Nachname</label>
                        <input type="text" class="form-control form_val" id="lastName" placeholder="" value="">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control form_val" id="email" placeholder="name@mail.com">
                </div>

                <button id="sendbtn" class="btn btn-primary btn-lg btn-block" >Meldung abschicken</button>
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

        // Check Abfrage ausführen
        $('#sendbtn').on('click', function (e) {
            e.preventDefault();
            var form_data = new FormData();
            $('.form_val').each(function() {
                form_data.append($( this ).attr('id'), $( this ).val());
            });
            $('.form_check_val').each(function() {
                form_data.append($( this ).attr('id'), $( this ).prop("checked"));
            });

            $.ajax({
                url: '/ajax/index.php', // point to server-side PHP script
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (php_script_response) {
                    alert(php_script_response); // display response from the PHP script, if any
                },
                error: function (jqXHR, exception) {
                    // bei Fehler Meldung ausgeben
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status === 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status === 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    alert(msg);
                }
            });
            });
        });
    </script>
</body>
</html>