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
        <p class="lead">Auf dieser Seite finden Sie Hinweise dazu, wo Sie Inhalte melden können, die Sie gemäß geltendem Recht aus unseren Diensten entfernen lassen möchten. Um Ihre Anfrage umfassend bearbeiten zu können möchten wir Sie bitten vollständige und ausführliche Angaben zum Sachverhalt zu machen.</p>
    </div>

    <div class="row">
        <div class="col-md-4 order-md-2 mb-4">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Your cart</span>
                <span class="badge badge-secondary badge-pill">3</span>
            </h4>
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between lh-condensed">
                    <div>
                        <h6 class="my-0">Product name</h6>
                        <small class="text-muted">Brief description</small>
                    </div>
                    <span class="text-muted">$12</span>
                </li>
                <li class="list-group-item d-flex justify-content-between lh-condensed">
                    <div>
                        <h6 class="my-0">Second product</h6>
                        <small class="text-muted">Brief description</small>
                    </div>
                    <span class="text-muted">$8</span>
                </li>
                <li class="list-group-item d-flex justify-content-between lh-condensed">
                    <div>
                        <h6 class="my-0">Third item</h6>
                        <small class="text-muted">Brief description</small>
                    </div>
                    <span class="text-muted">$5</span>
                </li>
                <li class="list-group-item d-flex justify-content-between bg-light">
                    <div class="text-success">
                        <h6 class="my-0">Promo code</h6>
                        <small>EXAMPLECODE</small>
                    </div>
                    <span class="text-success">-$5</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Total (USD)</span>
                    <strong>$20</strong>
                </li>
            </ul>

            <form class="card p-2">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Promo code">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-secondary">Redeem</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-8 order-md-1">
            <h4 class="mb-3">Billing address</h4>
            <form class="needs-validation" novalidate="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName">First name</label>
                        <input type="text" class="form-control" id="firstName" placeholder="" value="" required="">
                        <div class="invalid-feedback">
                            Valid first name is required.
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName">Last name</label>
                        <input type="text" class="form-control" id="lastName" placeholder="" value="" required="">
                        <div class="invalid-feedback">
                            Valid last name is required.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">@</span>
                        </div>
                        <input type="text" class="form-control" id="username" placeholder="Username" required="">
                        <div class="invalid-feedback" style="width: 100%;">
                            Your username is required.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email">Email <span class="text-muted">(Optional)</span></label>
                    <input type="email" class="form-control" id="email" placeholder="you@example.com">
                    <div class="invalid-feedback">
                        Please enter a valid email address for shipping updates.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" id="address" placeholder="1234 Main St" required="">
                    <div class="invalid-feedback">
                        Please enter your shipping address.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address2">Address 2 <span class="text-muted">(Optional)</span></label>
                    <input type="text" class="form-control" id="address2" placeholder="Apartment or suite">
                </div>

                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="country">Country</label>
                        <select class="custom-select d-block w-100" id="country" required="">
                            <option value="">Choose...</option>
                            <option>United States</option>
                        </select>
                        <div class="invalid-feedback">
                            Please select a valid country.
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="state">State</label>
                        <select class="custom-select d-block w-100" id="state" required="">
                            <option value="">Choose...</option>
                            <option>California</option>
                        </select>
                        <div class="invalid-feedback">
                            Please provide a valid state.
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="zip">Zip</label>
                        <input type="text" class="form-control" id="zip" placeholder="" required="">
                        <div class="invalid-feedback">
                            Zip code required.
                        </div>
                    </div>
                </div>
                <hr class="mb-4">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="same-address">
                    <label class="custom-control-label" for="same-address">Shipping address is the same as my billing address</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="save-info">
                    <label class="custom-control-label" for="save-info">Save this information for next time</label>
                </div>
                <hr class="mb-4">

                <h4 class="mb-3">Payment</h4>

                <div class="d-block my-3">
                    <div class="custom-control custom-radio">
                        <input id="credit" name="paymentMethod" type="radio" class="custom-control-input" checked="" required="">
                        <label class="custom-control-label" for="credit">Credit card</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input id="debit" name="paymentMethod" type="radio" class="custom-control-input" required="">
                        <label class="custom-control-label" for="debit">Debit card</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input id="paypal" name="paymentMethod" type="radio" class="custom-control-input" required="">
                        <label class="custom-control-label" for="paypal">PayPal</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cc-name">Name on card</label>
                        <input type="text" class="form-control" id="cc-name" placeholder="" required="">
                        <small class="text-muted">Full name as displayed on card</small>
                        <div class="invalid-feedback">
                            Name on card is required
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cc-number">Credit card number</label>
                        <input type="text" class="form-control" id="cc-number" placeholder="" required="">
                        <div class="invalid-feedback">
                            Credit card number is required
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="cc-expiration">Expiration</label>
                        <input type="text" class="form-control" id="cc-expiration" placeholder="" required="">
                        <div class="invalid-feedback">
                            Expiration date required
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="cc-cvv">CVV</label>
                        <input type="text" class="form-control" id="cc-cvv" placeholder="" required="">
                        <div class="invalid-feedback">
                            Security code required
                        </div>
                    </div>
                </div>
                <hr class="mb-4">
                <button class="btn btn-primary btn-lg btn-block" type="submit">Continue to checkout</button>
            </form>
        </div>
    </div>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">© 2017-2020 Company Name</p>
            <ul class="list-inline">
                <li class="list-inline-item"><a href="#">Privacy</a></li>
                <li class="list-inline-item"><a href="#">Terms</a></li>
                <li class="list-inline-item"><a href="#">Support</a></li>
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