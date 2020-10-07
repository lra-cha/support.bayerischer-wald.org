<?php
/*
 * Datei index.php
 * Endpoint der die Anfragen aus dem Suchformular aufnimmt aufnimmt
 */
error_reporting(E_ALL);
// header setzen JSON und Cross Origin
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
require '../vendor/autoload.php';
include_once('../common/settings.php');
use Elasticsearch\ClientBuilder;

// LIMIT für Suchergebnisse festlegen
define('RETURNLIMIT',SETTINGS['txtResults']);
// minimale Parameter prüfen
if(!isset($_GET['mode'])) {
    die(500);
}
// Elasticsearch client initialisieren
$elastic_index = 'bywald_org_geocode';
$client = ClientBuilder::create()->build();
$finalresponse= [];
$knowntexts= [];
// Mode auswerten und entsprechen reagieren
switch ($_GET['mode']){
    case 'cities':
        // suche nach Orten
        $response = $client->search(getCityQuery($elastic_index));
        foreach ($response['hits']['hits'] as $item) {
            if(!in_array($item['_source']['city'],$knowntexts)) { // zur Sicherheit um Dubletten zu vermeiden
                $knowntexts[] =$item['_source']['city'];
                $finalresponse[] = ['id' => $item['_id'], 'text' => $item['_source']['city'], 'zip' => $item['_source']['zip'], 'zip_uid' => $item['_source']['zip_uid']];
            }
        }
        break;
    case 'streets':
        // suche nach Straßen
        $response = $client->search(getStreetsQuery($elastic_index));
        foreach ($response['hits']['hits'] as $item) {
            if(!in_array($item['_source']['street'],$knowntexts)) { // zur Sicherheit um Dubletten zu vermeiden
                $knowntexts[] = $item['_source']['street'];
                $finalresponse[] = ['id' => $item['_id'], 'text' => $item['_source']['street'], 'zip_uid' => $item['_source']['zip_uid'], 'zip' => $item['_source']['zip'],'city_uid' => $item['_source']['city_uid'],'city' => $item['_source']['city']];
            }
        }
        break;
    case 'housenumbers':
        // suche nach Hausnummer
        // im Vorfeld minimalparameter prüfen
        if((!isset($_GET['street'])||$_GET['street']==='')) // no street no number
        {
            break;
        }
        $response = $client->search(getHousesQuery($elastic_index));
        foreach ($response['hits']['hits'] as $item) {
            if(!in_array($item['_source']['street_num_full'],$knowntexts)) { // zur Sicherheit um Dubletten zu vermeiden
                $knowntexts[] = $item['_source']['street_num_full'];
                $finalresponse[] = ['id' => $item['_id'], 'text' => $item['_source']['street_num_full']];
            }
        }
        break;
    case 'zipcodes':
        // suche nach Postleitzahl
        $response = $client->search(getZipQuery($elastic_index));
        foreach ($response['hits']['hits'] as $item) {
            if(!in_array($item['_source']['zip'],$knowntexts)) { // zur Sicherheit um Dubletten zu vermeiden
                $knowntexts[] = $item['_source']['zip'];
                $finalresponse[] = ['id' => $item['_id'], 'text' => $item['_source']['zip']];
            }
        }
        break;
    case 'validate':
        // überprüft die komplette ausgewählte Adresse und gleicht diese mit den Ausbaugebieten ab

        // $HTTP_X_FORWARDED_FOR = md5($_SERVER['HTTP_X_FORWARDED_FOR']);
        // Fingerprint des Clients (für Prüfung der Anfragezahl)
        $HTTP_X_FORWARDED_FOR='';
        $REMOTE_ADDR = md5($_SERVER['REMOTE_ADDR']);
        $timestamp = new DateTime(); // aktuellen Timestamp
        $laststamp = 2137385781; // letzten Timestamp in der Zukunft falls bisher keine Zugriffe
        $vresponse = [];
        // minimale Parameter prüfen
        if (!isset($_GET['zip'])||!isset($_GET['city'])||!isset($_GET['street'])||!isset($_GET['house']))
        {
            $vresponse['status'] = 'missing Parameter';
            $vresponse['result'] = [];
        }
        else
        {
            $mysqli = mysqli_connect("localhost", "di_check", "di_check", "di_check");
            if (mysqli_connect_errno()) {
                $vresponse['status'] = 'error db';
                $vresponse['result'] = [];
            }
            else {
                $date = date('Y-m-d H:i:s');
                // Abfrage ob passend zum Fingerprint bereits Anfragen protokolliert wurden
                // Offset auf maxRequests setzen da nur Anfragen interessant sind die die maximale Anzahl überschreiten
                $re = $mysqli->query("SELECT `reqTime2` FROM `requests` WHERE `REMOTE_ADDR` = '".$REMOTE_ADDR."' AND `HTTP_X_FORWARDED_FOR` = '".$HTTP_X_FORWARDED_FOR."' ORDER By rID DESC LIMIT 1," .(SETTINGS['txtMaxRequests']+1));
                // falls Anfragen vorhanden letzten Timestamp auf den tatsächlichen Wert setzen
                if($re->num_rows > 0)
                {
                    $row = $re->fetch_assoc();
                    $laststamp = (int)$row['reqTime2'];
                }
            }
            // Koordinate aus Elasticsearch abfragen
            $response = $client->search(getValidateQuery($elastic_index));

            // Antwort auswerten (sollte aber nur 1 Item sein)
            $vresponse['result'] = [];
            foreach ($response['hits']['hits'] as $item) {
                $vresponse['result'][] = ['id' => $item['_id'], '_source' => $item['_source']];
            }

            // mit den gelieferten Koordinaten nach passenden Ausbaugebieten suchen
            $response = $client->search(getGbtQuery($elastic_index,$vresponse['result'][0]['_source']['location']['lat'],$vresponse['result'][0]['_source']['location']['lon']));

            // alter der letzten letzten relevanten Suchanfrage berechnen und am response anhängen
            $age = $timestamp->getTimestamp()-$laststamp;
            $vresponse['age'] = $age;
            $vresponse['txtMaxRequestsTime'] = (int)SETTINGS['txtMaxRequestsTime'];
            $vresponse['txtMaxRequests'] = SETTINGS['txtMaxRequests'];

            // wenn das Alter zwichen 0 und dem Maximalen Alter liegt is alles okay
            if($age<1||$age>=(int)SETTINGS['txtMaxRequestsTime']) {
                $vresponse['result'][0]['_source']['avail'] = ($response['hits']['total']['value'] >= 1);
                $vresponse['status'] = 'found';
                $vresponse['stamp'] = $laststamp;

                if (mysqli_connect_errno()) {
                }
                else {
                    // neuen Eintrag ins suchlog schreiben
                    $date = date('Y-m-d H:i:s');
                    $re = $mysqli->query("INSERT INTO requests(`zip`, `city`, `street`,`house`, `found`, `rdate`,`HTTP_X_FORWARDED_FOR`,`REMOTE_ADDR`,`reqTime2`) VALUES ('".$_GET['zip']."', '".$_GET['city']."','".$_GET['street']."', '".$_GET['house']."', '".$vresponse['result'][0]['_source']['avail']."','".$date."','".$HTTP_X_FORWARDED_FOR."','".$REMOTE_ADDR."',".$timestamp->getTimestamp().")");
                }
            }
            else
            {
                // wenn zu viele responses dann gibts nur warten
                $vresponse['status'] = 'wait';
            }
            $vresponse['stamp'] = $laststamp;
            $mysqli->close();

        }
        break;
    case 'gbt':
        // zu Testzwecken
            if((!isset($_GET['lat'])||$_GET['lat']==='')) // no street no number
            {
                break;
            }
            $response = $client->search(getGbtQuery($elastic_index,$_GET['lat'],$_GET['lon']));
            echo json_encode($response);
            die(0);
        break;
    case 'gbtrange':
        // zu Testzwecken
        if((!isset($_GET['lat'])||$_GET['lat']==='')) // no street no number
        {
            break;
        }
        $response = $client->search(getGbtRangeQuery($elastic_index,$_GET['lat'],$_GET['lon'],$_GET['range']));
        echo json_encode($response);
        die(0);
        break;
    case 'gbtall':
        // zu Testzwecken
        if((!isset($_GET['lat'])||$_GET['lat']==='')) // no street no number
        {
            break;
        }
        $response = $client->search(getGbtAllQuery($elastic_index));
        echo json_encode($response);
        die(0);
        break;
    /*case 'gbttest':
        // zu Testzwecken
        $response = $client->search([
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'gbt']]
                        ]
                    ]
                ]
            ]
        ]);
        echo json_encode($response);
        die(0);
        break;
    case 'mappingtest':
        // zu Testzwecken
        $params = ['index' => $elastic_index];
        $response = $client->indices()->getMapping($params);
        echo json_encode($response);
        die(0);
        break;
    case 'test':
        // zu Testzwecken
        $response = $client->search([
            'index' => $elastic_index,
            'body' => [
                'size' => 1,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'full']],
                            ['term' => ['zip' => $_GET['zip']]],
                        ]
                    ]
                ]
            ]
        ]);
        echo json_encode($response);
        die(0);
        break;*/
    default:
        die(500);
}
if(isset($_GET['debug'])) // wenn debug den blanken response ausgeben
{
    echo json_encode($response);
    die(0);
}
if(isset($vresponse))
{
    echo json_encode($vresponse);
}
else {
    usort($finalresponse, 'compareByText');
    echo json_encode(['results' => $finalresponse]);
}


function compareByText($a, $b) {
    return strcasecmp ($a['text'], $b['text']);
}
function getGbtRangeQuery($elastic_index,$lat,$lon,$range)
{
    //dont have 2 check if all parameters are set cause it was checked b4
    $query = [
        'index' => $elastic_index,
        'body' => [
            'size' => RETURNLIMIT,
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['mode' => 'gbt']]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => $range.'m',
                            'location' => [
                                'lat' => $lat,
                                'lon' => $lon
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    return $query;
}
function getGbtQuery($elastic_index,$lat,$lon)
{
    //dont have 2 check if all parameters are set cause it was checked b4
    $query = [
        'index' => $elastic_index,
        'body' => [
            'size' => RETURNLIMIT,
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['mode' => 'gbt']]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => SETTINGS['txtAcc'].'m',
                            'location' => [
                                'lat' => $lat,
                                'lon' => $lon
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    return $query;
}
function getGbtAllQuery($elastic_index)
{
    //dont have 2 check if all parameters are set cause it was checked b4
    $query = [
        'index' => $elastic_index,
        'body' => [
            'size' => 2000,
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['mode' => 'gbt']]
                    ]
                ]
            ]
        ]
    ];
    return $query;
}
function getValidateQuery($elastic_index)
{
    //dont have 2 check if all parameters are set cause it was checked b4
    $query = [
        'index' => $elastic_index,
        'body' => [
            'size' => 1,
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['mode' => 'full']],
                        ['term' => ['zip' => $_GET['zip']]],

                        ['match_phrase' => ['street_key' => urldecode($_GET['street'])]],
                        ['match_phrase' => ['street_num_full' => urldecode($_GET['house'])]]
                    ]
                ]
            ]
        ]
    ];
    return $query;
}
function getCityQuery($elastic_index) {
    $query = [];
    $returnfields = ['city','zip', 'city_full', 'zip_uid'];
    if((!isset($_GET['zipcode'])||$_GET['zipcode']==='')&&(!isset($_GET['city'])||$_GET['city']==='')) {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => 15,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'city']]
                        ]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else if((isset($_GET['zipcode'])&&$_GET['zipcode']!=='')&&(!isset($_GET['city'])||$_GET['city']==='')) {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'city']],
                            ['term' => ['zip' => $_GET['zipcode']]]
                        ]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else if((!isset($_GET['zipcode'])||$_GET['zipcode']==='')&&(isset($_GET['city'])&&$_GET['city']!=='')) {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            'term' => ['mode' => 'city']
                        ],
                        'filter' => [
                            'match_phrase_prefix' => [
                                'city_type' => $_GET['city']
                            ]
                        ]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else if(!isset($_GET['city'])||$_GET['city']==='') {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'city']],
                            ['term' => ['zip' => $_GET['zipcode']]]
                        ]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'city']],
                            ['term' => ['zip' => $_GET['zipcode']]]
                        ],
                        'filter' => [
                            'match_phrase_prefix' => [
                                'city_type' => $_GET['city']
                            ]
                        ]
                    ]

                ]
            ],
            '_source' => $returnfields
        ];
    }
    return $query;
}
function getZipQuery($elastic_index) {
    $query = [];
    $returnfields = ['city','zip'];
    if(!isset($_GET['zipcode'])||$_GET['zipcode']==='') {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => ['term' => ['mode' => 'zipcode']]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => ['term' => ['mode' => 'zipcode']],
                        'filter' => [
                            'match_phrase_prefix' => [
                                'zip' => $_GET['zipcode']
                            ]
                        ]
                    ]

                ]
            ],
            '_source' => $returnfields
        ];
    }
    return $query;
}
function getStreetsQuery($elastic_index) {
    $query = [];
    // zipcode, city, street
    $returnfields = ['zip','street','city_key', 'city', 'zip_uid','city_uid'];
    if((!isset($_GET['city'])||$_GET['city']==='')&&(!isset($_GET['street'])||$_GET['street']==='')) {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'street']]
                        ]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else if((isset($_GET['city'])&&$_GET['city']!=='')&&(!isset($_GET['street'])||$_GET['street']==='')) {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'street']],
                            ['match_phrase' => ['city_key' => urldecode($_GET['city'])]]
                        ]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else if((!isset($_GET['city'])||$_GET['city']==='')&&(isset($_GET['street'])&&$_GET['street']!=='')) {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            'term' => ['mode' => 'street']
                        ],
                        'filter' => [
                            'match_phrase_prefix' => [
                                'street' => $_GET['street']
                            ]
                        ]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else if(!isset($_GET['city'])||$_GET['city']==='') {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'street']],
                            ['match_phrase' => ['city_key' => urldecode($_GET['city'])]]
                        ]
                    ]
                ]
            ],
            '_source' => $returnfields
        ];
    }
    else {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'street']],
                            ['match_phrase' => ['city_key' => urldecode($_GET['city'])]]
                        ],
                        'filter' => [
                            'match_phrase_prefix' => [
                                'street' => $_GET['street']
                            ]
                        ]
                    ]

                ]
            ],
            '_source' => $returnfields
        ];
    }
    return $query;
}
function getHousesQuery($elastic_index) {
    $query = [];
    // zipcode, street,house
    $returnfields = ['street_num_full'];
    if(!isset($_GET['house'])||$_GET['house']==='') {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => 150,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'housenumber']],
                            ['term' => ['zip' => $_GET['zipcode']]],
                            ['match_phrase' => ['street_key' => urldecode($_GET['street'])]]
                        ]
                    ]

                ]
            ],
            '_source' => $returnfields
        ];
    }
    else {
        $query = [
            'index' => $elastic_index,
            'body' => [
                'size' => RETURNLIMIT,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['mode' => 'housenumber']],
                            ['term' => ['zip' => $_GET['zipcode']]],
                            ['match_phrase' => ['street_key' => urldecode($_GET['street'])]]
                        ],
                        'filter' => [
                            'match_phrase_prefix' => [
                                'street_num_type' => $_GET['house']
                            ]
                        ]
                    ]

                ]
            ],
            '_source' => $returnfields
        ];
    }
    return $query;
}