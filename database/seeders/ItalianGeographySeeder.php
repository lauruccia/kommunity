<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItalianGeographySeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks so we can truncate safely
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::table('cities')->truncate();
        DB::table('provinces')->truncate();
        DB::table('regions')->truncate();

        DB::statement('PRAGMA foreign_keys = ON');

        DB::transaction(function (): void {
            $data = $this->getData();

            foreach ($data as $regionData) {
                // Upsert region
                DB::table('regions')->updateOrInsert(
                    ['code' => $regionData['code']],
                    [
                        'name'       => $regionData['name'],
                        'slug'       => Str::slug($regionData['name']),
                        'code'       => $regionData['code'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $regionId = DB::table('regions')->where('code', $regionData['code'])->value('id');

                foreach ($regionData['provinces'] as $provinceData) {
                    // Upsert province
                    DB::table('provinces')->updateOrInsert(
                        ['code' => $provinceData['code']],
                        [
                            'region_id'  => $regionId,
                            'name'       => $provinceData['name'],
                            'slug'       => Str::slug($provinceData['name']),
                            'code'       => $provinceData['code'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    $provinceId = DB::table('provinces')->where('code', $provinceData['code'])->value('id');

                    // Upsert cities — slug includes province code to avoid duplicates
                    // (Italy has many cities with the same name in different provinces)
                    foreach ($provinceData['cities'] as $cityName) {
                        $citySlug = Str::slug($cityName . '-' . $provinceData['code']);
                        DB::table('cities')->updateOrInsert(
                            ['slug' => $citySlug],
                            [
                                'region_id'   => $regionId,
                                'province_id' => $provinceId,
                                'name'        => $cityName,
                                'slug'        => $citySlug,
                                'province'    => $provinceData['code'],
                                'postal_code' => null,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]
                        );
                    }
                }
            }
        });
    }

    private function getData(): array
    {
        return [
            // ─────────────────────────────────────────────────────────────
            // 1. VALLE D'AOSTA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => "Valle d'Aosta",
                'code' => 'VDA',
                'provinces' => [
                    [
                        'name' => "Aosta",
                        'code' => 'AO',
                        'cities' => [
                            'Aosta', 'Brissogne', 'Charvensod', 'Cogne', 'Courmayeur',
                            'Donnas', 'Gignod', 'Gressan', 'Jovençan', 'Montjovet',
                            'Morgex', 'Nus', 'Pollein', 'Pont-Saint-Martin', "Quart",
                            "Saint-Christophe", "Saint-Vincent", "Sarre", "Verrès", "Villeneuve",
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 2. PIEMONTE
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Piemonte',
                'code' => 'PIE',
                'provinces' => [
                    [
                        'name' => 'Torino',
                        'code' => 'TO',
                        'cities' => [
                            'Torino', 'Moncalieri', 'Collegno', 'Grugliasco', 'Settimo Torinese',
                            'Nichelino', 'Rivoli', 'Chieri', 'Pinerolo', 'Venaria Reale',
                            'Alpignano', 'Borgaro Torinese', 'Carmagnola', 'Chivasso', 'Cirié',
                            'Druento', 'Giaveno', 'Ivrea', 'Leini', 'Mappano',
                            'Orbassano', 'Pianezza', 'Piossasco', 'Poirino', 'Rivarolo Canavese',
                            'Rivalta di Torino', 'San Mauro Torinese', 'Santena', 'Susa', 'Torre Pellice',
                            'Trofarello', 'Vinovo', 'Volpiano', 'Avigliana', 'Beinasco',
                            'Brandizzo', 'Candiolo', 'Caselle Torinese', 'Cavour', 'Cuorgnè',
                            'Gassino Torinese', 'La Loggia', 'Luserna San Giovanni', 'Mathi', 'Nole',
                            'None', 'Pecetto Torinese', 'Rosta', 'San Gillio', 'Villar Perosa',
                        ],
                    ],
                    [
                        'name' => 'Alessandria',
                        'code' => 'AL',
                        'cities' => [
                            'Alessandria', 'Casale Monferrato', 'Novi Ligure', 'Tortona', 'Valenza',
                            'Acqui Terme', 'Asti (confine)', 'Castellazzo Bormida', 'Ovada', 'Pontecurone',
                            'San Giuliano Nuovo', 'Serravalle Scrivia', 'Spinetta Marengo', 'Viguzzolo', 'Voghera (confine)',
                        ],
                    ],
                    [
                        'name' => 'Asti',
                        'code' => 'AT',
                        'cities' => [
                            'Asti', 'Canelli', 'Nizza Monferrato', 'San Damiano d\'Asti', 'Villanova d\'Asti',
                            'Moncalvo', 'Costigliole d\'Asti', 'Castelnuovo Don Bosco', 'Montechiaro d\'Asti', 'Rocchetta Tanaro',
                        ],
                    ],
                    [
                        'name' => 'Biella',
                        'code' => 'BI',
                        'cities' => [
                            'Biella', 'Cossato', 'Gaglianico', 'Candelo', 'Vigliano Biellese',
                            'Borriana', 'Ponderano', 'Ronco Biellese', 'Trivero', 'Valle Mosso',
                        ],
                    ],
                    [
                        'name' => 'Cuneo',
                        'code' => 'CN',
                        'cities' => [
                            'Cuneo', 'Alba', 'Bra', 'Fossano', 'Mondovì',
                            'Saluzzo', 'Busca', 'Canale', 'Caraglio', 'Cavallermaggiore',
                            'Centallo', 'Cherasco', 'Dronero', 'Gaiola', 'Racconigi',
                            'Savigliano', 'Vignolo', 'Villafalletto', 'Borgo San Dalmazzo', 'Dogliani',
                        ],
                    ],
                    [
                        'name' => 'Novara',
                        'code' => 'NO',
                        'cities' => [
                            'Novara', 'Borgomanero', 'Galliate', 'Arona', 'Cameri',
                            'Oleggio', 'Trecate', 'Romentino', 'Varallo Pombia', 'Bellinzago Novarese',
                            'Dormelletto', 'Gozzano', 'Marano Ticino', 'Momo', 'Romagnano Sesia',
                        ],
                    ],
                    [
                        'name' => 'Verbano-Cusio-Ossola',
                        'code' => 'VB',
                        'cities' => [
                            'Verbania', 'Domodossola', 'Omegna', 'Baveno', 'Gravellona Toce',
                            'Stresa', 'Cannobio', 'Ghiffa', 'Intra', 'Pallanza',
                        ],
                    ],
                    [
                        'name' => 'Vercelli',
                        'code' => 'VC',
                        'cities' => [
                            'Vercelli', 'Borgosesia', 'Gattinara', 'Santhià', 'Trino',
                            'Crescentino', 'Cigliano', 'Caresana', 'Livorno Ferraris', 'Varallo',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 3. LIGURIA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Liguria',
                'code' => 'LIG',
                'provinces' => [
                    [
                        'name' => 'Genova',
                        'code' => 'GE',
                        'cities' => [
                            'Genova', 'Rapallo', 'Chiavari', 'Recco', 'Sestri Levante',
                            'Arenzano', 'Bogliasco', 'Camogli', 'Casarza Ligure', 'Cogoleto',
                            'Lavagna', 'Ne', 'Paraggi', 'Pieve Ligure', 'Santa Margherita Ligure',
                            'Sori', 'Tiglieto', 'Zoagli', 'Portofino', 'Uscio',
                        ],
                    ],
                    [
                        'name' => 'Imperia',
                        'code' => 'IM',
                        'cities' => [
                            'Imperia', 'Sanremo', 'Ventimiglia', 'Bordighera', 'Diano Marina',
                            'Taggia', 'Vallecrosia', 'Albenga (confine)', 'Camporosso', 'Ospedaletti',
                            'Riva Ligure', 'San Bartolomeo al Mare', 'Santo Stefano al Mare',
                        ],
                    ],
                    [
                        'name' => 'La Spezia',
                        'code' => 'SP',
                        'cities' => [
                            'La Spezia', 'Sarzana', 'Lerici', 'Portovenere', 'Ameglia',
                            'Arcola', 'Brugnato', 'Calice al Cornoviglio', 'Carro', 'Castelnuovo Magra',
                            'Follo', 'Levanto', 'Monterosso al Mare', 'Riomaggiore', 'Santo Stefano di Magra',
                        ],
                    ],
                    [
                        'name' => 'Savona',
                        'code' => 'SV',
                        'cities' => [
                            'Savona', 'Albenga', 'Alassio', 'Finale Ligure', 'Loano',
                            'Cairo Montenotte', 'Pietra Ligure', 'Vado Ligure', 'Celle Ligure', 'Spotorno',
                            'Varazze', 'Borghetto Santo Spirito', 'Ceriale', 'Millesimo', 'Quiliano',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 4. LOMBARDIA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Lombardia',
                'code' => 'LOM',
                'provinces' => [
                    [
                        'name' => 'Milano',
                        'code' => 'MI',
                        'cities' => [
                            'Milano', 'Sesto San Giovanni', 'Cinisello Balsamo', 'Monza (confine)', 'Rho',
                            'Corsico', 'Cologno Monzese', 'Pero', 'Pioltello', 'Segrate',
                            'Opera', 'Cernusco sul Naviglio', 'Locate di Triulzi', 'Assago', 'Baranzate',
                            'Basiglio', 'Bresso', 'Buccinasco', 'Cesano Boscone', 'Cormano',
                            'Cornaredo', 'Cusago', 'Garbagnate Milanese', 'Lainate', 'Legnano',
                            'Limbiate', 'Lodi Vecchio', 'Magenta', 'Melegnano', 'Melzo',
                            'Nerviano', 'Novate Milanese', 'Paderno Dugnano', 'Pantigliate', 'Parabiago',
                            'Peschiera Borromeo', 'Pregnana Milanese', 'Robecco sul Naviglio', 'Rodano', 'Rozzano',
                            'San Donato Milanese', 'San Giorgio su Legnano', 'San Giuliano Milanese', 'Senago', 'Settala',
                            'Settimo Milanese', 'Trezzano sul Naviglio', 'Trezzo sull\'Adda', 'Vimodrone', 'Vizzolo Predabissi',
                        ],
                    ],
                    [
                        'name' => 'Bergamo',
                        'code' => 'BG',
                        'cities' => [
                            'Bergamo', 'Treviglio', 'Romano di Lombardia', 'Dalmine', 'Seriate',
                            'Caravaggio', 'Grumello del Monte', 'Nembro', 'Ponte San Pietro', 'Stezzano',
                            'Torre Boldone', 'Villa d\'Almè', 'Alzano Lombardo', 'Clusone', 'Gandino',
                            'Lovere', 'Scanzorosciate', 'Somasca', 'Verdello', 'Zanica',
                            'Albino', 'Brembate', 'Calusco d\'Adda', 'Ciserano', 'Comun Nuovo',
                            'Costa Volpino', 'Lallio', 'Osio Sotto', 'Pedrengo', 'San Paolo d\'Argon',
                        ],
                    ],
                    [
                        'name' => 'Brescia',
                        'code' => 'BS',
                        'cities' => [
                            'Brescia', 'Desenzano del Garda', 'Montichiari', 'Chiari', 'Concesio',
                            'Gardone Val Trompia', 'Lonato del Garda', 'Lumezzane', 'Rezzato', 'Rovato',
                            'Sirmione', 'Travagliato', 'Bagnolo Mella', 'Bedizzole', 'Botticino',
                            'Calcinato', 'Carpenedolo', 'Castenedolo', 'Cellatica', 'Cologne',
                            'Fiesse', 'Flero', 'Gambara', 'Gavardo', 'Ghedi',
                            'Gottolengo', 'Leno', 'Manerbio', 'Marone', 'Nave',
                            'Nuvolento', 'Palazzolo sull\'Oglio', 'Pisogne', 'Pontevico', 'Remedello',
                            'San Felice del Benaco', 'Salò', 'Soiano del Lago', 'Verolanuova', 'Villa Carcina',
                        ],
                    ],
                    [
                        'name' => 'Como',
                        'code' => 'CO',
                        'cities' => [
                            'Como', 'Cantù', 'Mariano Comense', 'Erba', 'Olgiate Comasco',
                            'Appiano Gentile', 'Binago', 'Cabiate', 'Carimate', 'Casnate con Bernate',
                            'Cermenate', 'Colverde', 'Guanzate', 'Lurago d\'Erba', 'Mozzate',
                            'Novedrate', 'Senna Comasco', 'Uggiate-Trevano', 'Varenna', 'Villa Guardia',
                        ],
                    ],
                    [
                        'name' => 'Cremona',
                        'code' => 'CR',
                        'cities' => [
                            'Cremona', 'Crema', 'Casalmaggiore', 'Soresina', 'Pandino',
                            'Castelleone', 'Offanengo', 'Rivolta d\'Adda', 'Romanengo', 'Trescore Cremasco',
                            'Bagnolo Cremasco', 'Capralba', 'Dovera', 'Fiesco', 'Izano',
                        ],
                    ],
                    [
                        'name' => 'Lecco',
                        'code' => 'LC',
                        'cities' => [
                            'Lecco', 'Merate', 'Oggiono', 'Calolziocorte', 'Casatenovo',
                            'Cernusco Lombardone', 'Cesana Brianza', 'Colico', 'Galbiate', 'Malgrate',
                            'Mandello del Lario', 'Missaglia', 'Olginate', 'Osnago', 'Varenna',
                        ],
                    ],
                    [
                        'name' => 'Lodi',
                        'code' => 'LO',
                        'cities' => [
                            'Lodi', 'Codogno', 'Sant\'Angelo Lodigiano', 'Casalpusterlengo', 'Castiglione d\'Adda',
                            'Lodi Vecchio', 'Maleo', 'Orio Litta', 'Ospedaletto Lodigiano', 'Tavazzano con Villavesco',
                        ],
                    ],
                    [
                        'name' => 'Mantova',
                        'code' => 'MN',
                        'cities' => [
                            'Mantova', 'Guidizzolo', 'Suzzara', 'Castiglione delle Stiviere', 'Gonzaga',
                            'Goito', 'Bozzolo', 'Curtatone', 'Medole', 'Moglia',
                            'Ostiglia', 'Porto Mantovano', 'Roverbella', 'San Giorgio Bigarello', 'Sermide e Felonica',
                            'Viadana', 'Virgilio', 'Asola', 'Borgoforte', 'Mariana Mantovana',
                        ],
                    ],
                    [
                        'name' => 'Monza e della Brianza',
                        'code' => 'MB',
                        'cities' => [
                            'Monza', 'Desio', 'Seregno', 'Cesano Maderno', 'Lissone',
                            'Carate Brianza', 'Vimercate', 'Muggiò', 'Nova Milanese', 'Brugherio',
                            'Agrate Brianza', 'Arcore', 'Bellusco', 'Bernareggio', 'Besana in Brianza',
                            'Biassono', 'Bovisio-Masciago', 'Burago di Molgora', 'Carnate', 'Cavenago di Brianza',
                            'Ceriano Laghetto', 'Concorezzo', 'Correzzana', 'Giussano', 'Lentate sul Seveso',
                            'Lesmo', 'Limbiate', 'Macherio', 'Meda', 'Ornago',
                            'Renate', 'Ronco Briantino', 'Sovico', 'Sulbiate', 'Triuggio',
                            'Usmate Velate', 'Varedo', 'Verano Brianza', 'Villasanta', 'Withdraw',
                        ],
                    ],
                    [
                        'name' => 'Pavia',
                        'code' => 'PV',
                        'cities' => [
                            'Pavia', 'Vigevano', 'Voghera', 'Mede', 'Mortara',
                            'Stradella', 'Abbiategrasso', 'Bereguardo', 'Broni', 'Cassolnovo',
                            'Certosa di Pavia', 'Gambolò', 'Garlasco', 'Landriano', 'Pieve Emanuele',
                            'Robbio', 'San Martino Siccomario', 'Sannazzaro de\' Burgondi', 'Siziano', 'Tortona (confine)',
                            'Travacò Siccomario', 'Varzi', 'Zerbo',
                        ],
                    ],
                    [
                        'name' => 'Sondrio',
                        'code' => 'SO',
                        'cities' => [
                            'Sondrio', 'Morbegno', 'Tirano', 'Chiavenna', 'Bormio',
                            'Ardenno', 'Cosio Valtellino', 'Gordona', 'Livigno', 'Lovero',
                            'Samolaco', 'Talamona', 'Teglio', 'Villa di Tirano',
                        ],
                    ],
                    [
                        'name' => 'Varese',
                        'code' => 'VA',
                        'cities' => [
                            'Varese', 'Busto Arsizio', 'Gallarate', 'Saronno', 'Castellanza',
                            'Cassano Magnago', 'Tradate', 'Luino', 'Gallarate', 'Angera',
                            'Azzate', 'Bisuschio', 'Bodio Lomnago', 'Buguggiate', 'Caronno Pertusella',
                            'Casorate Sempione', 'Cavaria con Premezzo', 'Cislago', 'Cunardo', 'Fagnano Olona',
                            'Gallarate', 'Germignaga', 'Gorla Maggiore', 'Gorla Minore', 'Gazzada Schianno',
                            'Induno Olona', 'Jerago con Orago', 'Laveno-Mombello', 'Lonate Pozzolo', 'Malnate',
                            'Marchirolo', 'Marnate', 'Mercallo', 'Oggiona con Santo Stefano', 'Olgiate Olona',
                            'Sesto Calende', 'Solaro', 'Somma Lombardo', 'Uboldo', 'Vedano Olona',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 5. TRENTINO-ALTO ADIGE
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Trentino-Alto Adige',
                'code' => 'TAA',
                'provinces' => [
                    [
                        'name' => 'Trento',
                        'code' => 'TN',
                        'cities' => [
                            'Trento', 'Rovereto', 'Pergine Valsugana', 'Riva del Garda', 'Arco',
                            'Mori', 'Aldeno', 'Besenello', 'Calliano', 'Cles',
                            'Lavis', 'Ledro', 'Levico Terme', 'Mezzolombardo', 'Nomi',
                            'Pieve di Bono-Prezzo', 'Romagnano', 'Roncafort', 'Tione di Trento', 'Trambileno',
                            'Vallelaghi', 'Vermiglio', 'Ville d\'Anaunia', 'Vigolo Vattaro', 'Volano',
                        ],
                    ],
                    [
                        'name' => 'Bolzano',
                        'code' => 'BZ',
                        'cities' => [
                            'Bolzano', 'Merano', 'Bressanone', 'Brunico', 'Laives',
                            'Appiano sulla Strada del Vino', 'Caldaro sulla Strada del Vino', 'Campo di Trens', 'Chienes', 'Cornedo all\'Isarco',
                            'Egna', 'Falzes', 'Lana', 'Malles Venosta', 'Marlengo',
                            'Nalles', 'Naturno', 'Nova Levante', 'Ortisei', 'Postal',
                            'Rasun-Anterselva', 'Renon', 'Sarentino', 'Scena', 'Silandro',
                            'Termeno sulla Strada del Vino', 'Tires', 'Tirolo', 'Velturno', 'Vipiteno',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 6. VENETO
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Veneto',
                'code' => 'VEN',
                'provinces' => [
                    [
                        'name' => 'Venezia',
                        'code' => 'VE',
                        'cities' => [
                            'Venezia', 'Mestre', 'Chioggia', 'San Donà di Piave', 'Mirano',
                            'Spinea', 'Mogliano Veneto', 'Jesolo', 'Marcon', 'Martellago',
                            'Noale', 'Pianiga', 'Portogruaro', 'Salzano', 'Santa Maria di Sala',
                            'Vigonovo', 'Campagna Lupia', 'Cavarzere', 'Cona', 'Dolo',
                            'Eraclea', 'Favaro Veneto', 'Fiesso d\'Artico', 'Fossò', 'Iesolo',
                        ],
                    ],
                    [
                        'name' => 'Verona',
                        'code' => 'VR',
                        'cities' => [
                            'Verona', 'Villafranca di Verona', 'Legnago', 'San Bonifacio', 'Nogara',
                            'Bussolengo', 'Isola della Scala', 'Peschiera del Garda', 'Sona', 'Sommacampagna',
                            'Bardolino', 'Bovolone', 'Caldiero', 'Castel d\'Azzano', 'Cerea',
                            'Dossobuono', 'Garda', 'Grezzana', 'Lazise', 'Malcesine',
                            'Mozzecane', 'Oppeano', 'Pescantina', 'San Martino Buon Albergo', 'San Pietro in Cariano',
                            'Sanguinetto', 'Soave', 'Torri del Benaco', 'Valeggio sul Mincio', 'Vigasio',
                        ],
                    ],
                    [
                        'name' => 'Vicenza',
                        'code' => 'VI',
                        'cities' => [
                            'Vicenza', 'Bassano del Grappa', 'Valdagno', 'Schio', 'Thiene',
                            'Arzignano', 'Lonigo', 'Montecchio Maggiore', 'Montecchio Precalcino', 'Nove',
                            'Breganze', 'Brendola', 'Brogliano', 'Chiampo', 'Cornedo Vicentino',
                            'Dueville', 'Malo', 'Marostica', 'Mason Vicentino', 'Montorso Vicentino',
                            'Noventa Vicentina', 'Piovene Rocchette', 'Quinto Vicentino', 'Sandrigo', 'Torri di Quartesolo',
                            'Trissino', 'Valmarana', 'Vicenza (Casale)', 'Villaga', 'Zugliano',
                        ],
                    ],
                    [
                        'name' => 'Padova',
                        'code' => 'PD',
                        'cities' => [
                            'Padova', 'Abano Terme', 'Cittadella', 'Este', 'Monselice',
                            'Montagnana', 'Selvazzano Dentro', 'Albignasego', 'Borgoricco', 'Camposampiero',
                            'Carmignano di Brenta', 'Casalserugo', 'Conselve', 'Correzzola', 'Dolo (confine)',
                            'Grantorto', 'Limena', 'Massanzago', 'Mestrino', 'Montegrotto Terme',
                            'Noventa Padovana', 'Piazzola sul Brenta', 'Piove di Sacco', 'Ponte San Nicolò', 'Rubano',
                            'Saccolongo', 'Saonara', 'Sarmeola', 'Trebaseleghe', 'Vigonza',
                        ],
                    ],
                    [
                        'name' => 'Treviso',
                        'code' => 'TV',
                        'cities' => [
                            'Treviso', 'Conegliano', 'Vittorio Veneto', 'Montebelluna', 'Castelfranco Veneto',
                            'Mogliano Veneto (TV)', 'Oderzo', 'Pieve di Soligo', 'Asolo', 'Caerano di San Marco',
                            'Carbonera', 'Casier', 'Colle Umberto', 'Cordignano', 'Cornuda',
                            'Istrana', 'Maserada sul Piave', 'Monastier di Treviso', 'Morgano', 'Paese',
                            'Ponzano Veneto', 'Preganziol', 'Quinto di Treviso', 'Resana', 'San Biagio di Callalta',
                            'San Vendemiano', 'Silea', 'Spresiano', 'Susegana', 'Volpago del Montello',
                        ],
                    ],
                    [
                        'name' => 'Belluno',
                        'code' => 'BL',
                        'cities' => [
                            'Belluno', 'Feltre', 'Pieve di Cadore', 'Cortina d\'Ampezzo', 'Sedico',
                            'Longarone', 'Mel', 'Ponte nelle Alpi', 'Santa Giustina', 'Trichiana',
                        ],
                    ],
                    [
                        'name' => 'Rovigo',
                        'code' => 'RO',
                        'cities' => [
                            'Rovigo', 'Adria', 'Porto Tolle', 'Badia Polesine', 'Lendinara',
                            'Occhiobello', 'Porto Viro', 'Ariano nel Polesine', 'Crespino', 'Fiesso Umbertiano',
                            'Loreo', 'Villadose',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 7. FRIULI-VENEZIA GIULIA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Friuli-Venezia Giulia',
                'code' => 'FVG',
                'provinces' => [
                    [
                        'name' => 'Trieste',
                        'code' => 'TS',
                        'cities' => [
                            'Trieste', 'Duino-Aurisina', 'Monrupino', 'Muggia', 'San Dorligo della Valle',
                            'Sgonico',
                        ],
                    ],
                    [
                        'name' => 'Udine',
                        'code' => 'UD',
                        'cities' => [
                            'Udine', 'Cividale del Friuli', 'Codroipo', 'Gemona del Friuli', 'Latisana',
                            'Lignano Sabbiadoro', 'Palmanova', 'San Daniele del Friuli', 'Tolmezzo', 'Tricesimo',
                            'Buia', 'Fagagna', 'Gonars', 'Majano', 'Martignacco',
                            'Pasian di Prato', 'Pozzuolo del Friuli', 'Pradamano', 'Reana del Rojale', 'Tavagnacco',
                        ],
                    ],
                    [
                        'name' => 'Gorizia',
                        'code' => 'GO',
                        'cities' => [
                            'Gorizia', 'Monfalcone', 'Gradisca d\'Isonzo', 'Cormons', 'Staranzano',
                            'Ronchi dei Legionari', 'Sagrado', 'San Canzian d\'Isonzo', 'San Pier d\'Isonzo',
                        ],
                    ],
                    [
                        'name' => 'Pordenone',
                        'code' => 'PN',
                        'cities' => [
                            'Pordenone', 'Sacile', 'Maniago', 'San Vito al Tagliamento', 'Spilimbergo',
                            'Azzano Decimo', 'Brugnera', 'Casarsa della Delizia', 'Fontanafredda', 'Pasiano di Pordenone',
                            'Prata di Pordenone', 'Pravisdomini', 'Zoppola',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 8. EMILIA-ROMAGNA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Emilia-Romagna',
                'code' => 'EMR',
                'provinces' => [
                    [
                        'name' => 'Bologna',
                        'code' => 'BO',
                        'cities' => [
                            'Bologna', 'Imola', 'Casalecchio di Reno', 'San Lazzaro di Savena', 'Castel Maggiore',
                            'Pianoro', 'Zola Predosa', 'Sasso Marconi', 'Granarolo dell\'Emilia', 'Calderara di Reno',
                            'Anzola dell\'Emilia', 'Argelato', 'Bentivoglio', 'Budrio', 'Castel San Pietro Terme',
                            'Castenaso', 'Crevalcore', 'Dozza', 'Loiano', 'Marzabotto',
                            'Minerbio', 'Molinella', 'Monghidoro', 'Monte San Pietro', 'Monterenzio',
                            'Pieve di Cento', 'Sala Bolognese', 'San Giorgio di Piano', 'San Giovanni in Persiceto', 'San Pietro in Casale',
                            'Ozzano dell\'Emilia', 'Pianoro', 'Savigno', 'Vergato',
                        ],
                    ],
                    [
                        'name' => 'Ferrara',
                        'code' => 'FE',
                        'cities' => [
                            'Ferrara', 'Cento', 'Argenta', 'Comacchio', 'Codigoro',
                            'Bondeno', 'Copparo', 'Lagosanto', 'Mesola', 'Ostellato',
                            'Poggio Renatico', 'Portomaggiore', 'Sant\'Agostino',
                        ],
                    ],
                    [
                        'name' => 'Forlì-Cesena',
                        'code' => 'FC',
                        'cities' => [
                            'Forlì', 'Cesena', 'Cesenatico', 'Savignano sul Rubicone', 'Bertinoro',
                            'Castrocaro Terme e Terra del Sole', 'Forlimpopoli', 'Galeata', 'Gambettola',
                            'Longiano', 'Mercato Saraceno', 'Meldola', 'Rocca San Casciano', 'Sarsina',
                            'Sogliano al Rubicone',
                        ],
                    ],
                    [
                        'name' => 'Modena',
                        'code' => 'MO',
                        'cities' => [
                            'Modena', 'Carpi', 'Sassuolo', 'Formigine', 'Maranello',
                            'Mirandola', 'Castelfranco Emilia', 'Fiorano Modenese', 'Nonantola', 'Pavullo nel Frignano',
                            'Bastiglia', 'Bomporto', 'Camposanto', 'Castelvetro di Modena', 'Concordia sulla Secchia',
                            'Fanano', 'Finale Emilia', 'Guiglia', 'Lama Mocogno', 'Medolla',
                            'Montese', 'Prignano sulla Secchia', 'Ravarino', 'San Cesario sul Panaro', 'San Felice sul Panaro',
                            'San Possidonio', 'Savignano sul Panaro', 'Soliera', 'Spilamberto', 'Vignola',
                        ],
                    ],
                    [
                        'name' => 'Parma',
                        'code' => 'PR',
                        'cities' => [
                            'Parma', 'Fidenza', 'Salsomaggiore Terme', 'Collecchio', 'Langhirano',
                            'Traversetolo', 'Berceto', 'Borgo Val di Taro', 'Medesano', 'Montechiarugolo',
                            'Noceto', 'Parma', 'Polesine Zibello', 'Soragna', 'Sorbolo Mezzani',
                            'Torrile', 'Varano de\' Melegari', 'Zibello',
                        ],
                    ],
                    [
                        'name' => 'Piacenza',
                        'code' => 'PC',
                        'cities' => [
                            'Piacenza', 'Fiorenzuola d\'Arda', 'Castel San Giovanni', 'Caorso', 'Cortemaggiore',
                            'Castelvetro Piacentino', 'Gossolengo', 'Gragnano Trebbiense', 'Lugagnano Val d\'Arda', 'Monticelli d\'Ongina',
                            'Podenzano', 'Pontenure', 'Rottofreno', 'Sarmato', 'Villanova sull\'Arda',
                        ],
                    ],
                    [
                        'name' => 'Ravenna',
                        'code' => 'RA',
                        'cities' => [
                            'Ravenna', 'Faenza', 'Lugo', 'Cervia', 'Alfonsine',
                            'Bagnacavallo', 'Bagnara di Romagna', 'Casola Valsenio', 'Castel Bolognese', 'Conselice',
                            'Cotignola', 'Fusignano', 'Massa Lombarda', 'Riolo Terme', 'Russi',
                            'Sant\'Agata sul Santerno', 'Solarolo',
                        ],
                    ],
                    [
                        'name' => 'Reggio nell\'Emilia',
                        'code' => 'RE',
                        'cities' => [
                            'Reggio nell\'Emilia', 'Guastalla', 'Scandiano', 'Correggio', 'Castelnovo ne\' Monti',
                            'Montecchio Emilia', 'Campagnola Emilia', 'Cavriago', 'Correggio', 'Gattatico',
                            'Gualtieri', 'Luzzara', 'Novellara', 'Poviglio', 'Reggiolo',
                            'Rio Saliceto', 'Rolo', 'Rubiera', 'Sant\'Ilario d\'Enza', 'Villa Minozzo',
                        ],
                    ],
                    [
                        'name' => 'Rimini',
                        'code' => 'RN',
                        'cities' => [
                            'Rimini', 'Riccione', 'Cattolica', 'Bellaria-Igea Marina', 'Santarcangelo di Romagna',
                            'Misano Adriatico', 'Coriano', 'Gemmano', 'Mondaino', 'Monte Colombo',
                            'Montefiore Conca', 'Montegridolfo', 'Morciano di Romagna', 'Novafeltria', 'Pennabilli',
                            'Poggio Berni', 'San Clemente', 'San Giovanni in Marignano', 'Torriana', 'Verucchio',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 9. TOSCANA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Toscana',
                'code' => 'TOS',
                'provinces' => [
                    [
                        'name' => 'Firenze',
                        'code' => 'FI',
                        'cities' => [
                            'Firenze', 'Empoli', 'Scandicci', 'Sesto Fiorentino', 'Campi Bisenzio',
                            'Bagno a Ripoli', 'Calenzano', 'Castelfiorentino', 'Figline e Incisa Valdarno', 'Greve in Chianti',
                            'Lastra a Signa', 'Montelupo Fiorentino', 'Montespertoli', 'Pontassieve', 'Reggello',
                            'Rignano sull\'Arno', 'Rufina', 'San Casciano in Val di Pesa', 'Signa', 'Vinci',
                            'Borgo San Lorenzo', 'Dicomano', 'Fiesole', 'Fucecchio', 'Gambassi Terme',
                            'Impruneta', 'Londa', 'Marradi', 'Pelago', 'Vicchio',
                        ],
                    ],
                    [
                        'name' => 'Arezzo',
                        'code' => 'AR',
                        'cities' => [
                            'Arezzo', 'Cortona', 'Sansepolcro', 'Bibbiena', 'Capolona',
                            'Castiglion Fiorentino', 'Civitella in Val di Chiana', 'Foiano della Chiana', 'Laterina Pergine Valdarno', 'Monte San Savino',
                            'Montevarchi', 'Poppi', 'San Giovanni Valdarno', 'Subbiano', 'Terranuova Bracciolini',
                        ],
                    ],
                    [
                        'name' => 'Grosseto',
                        'code' => 'GR',
                        'cities' => [
                            'Grosseto', 'Orbetello', 'Follonica', 'Castiglione della Pescaia', 'Monte Argentario',
                            'Massa Marittima', 'Manciano', 'Pitigliano', 'Scarlino', 'Sorano',
                        ],
                    ],
                    [
                        'name' => 'Livorno',
                        'code' => 'LI',
                        'cities' => [
                            'Livorno', 'Piombino', 'Cecina', 'Rosignano Marittimo', 'Portoferraio',
                            'Campo nell\'Elba', 'Capoliveri', 'Castagneto Carducci', 'Collesalvetti', 'Marciana',
                            'Marciana Marina', 'Porto Azzurro', 'Rio', 'Sassetta',
                        ],
                    ],
                    [
                        'name' => 'Lucca',
                        'code' => 'LU',
                        'cities' => [
                            'Lucca', 'Viareggio', 'Capannori', 'Pietrasanta', 'Altopascio',
                            'Bagni di Lucca', 'Barga', 'Camaiore', 'Castelnuovo di Garfagnana', 'Forte dei Marmi',
                            'Massarosa', 'Montecarlo', 'Pescaglia', 'Porcari', 'Seravezza',
                            'Stazzema', 'Villa Basilica',
                        ],
                    ],
                    [
                        'name' => 'Massa-Carrara',
                        'code' => 'MS',
                        'cities' => [
                            'Massa', 'Carrara', 'Pontremoli', 'Aulla', 'Fivizzano',
                            'Fosdinovo', 'Licciana Nardi', 'Mulazzo', 'Podenzana', 'Villafranca in Lunigiana',
                            'Zeri',
                        ],
                    ],
                    [
                        'name' => 'Pisa',
                        'code' => 'PI',
                        'cities' => [
                            'Pisa', 'Pontedera', 'Cascina', 'San Miniato', 'Volterra',
                            'Calci', 'Calcinaia', 'Castelfranco di Sotto', 'Montopoli in Val d\'Arno', 'Palaia',
                            'Ponsacco', 'Pomarance', 'Santa Croce sull\'Arno', 'Santa Maria a Monte', 'Vicopisano',
                        ],
                    ],
                    [
                        'name' => 'Pistoia',
                        'code' => 'PT',
                        'cities' => [
                            'Pistoia', 'Pescia', 'Monsummano Terme', 'Montecatini Terme', 'Quarrata',
                            'Abetone Cutigliano', 'Agliana', 'Buggiano', 'Lamporecchio', 'Larciano',
                            'Marliana', 'Massa e Cozzile', 'Pieve a Nievole', 'Sambuca Pistoiese', 'Serravalle Pistoiese',
                            'Uzzano', 'Ponte Buggianese',
                        ],
                    ],
                    [
                        'name' => 'Prato',
                        'code' => 'PO',
                        'cities' => [
                            'Prato', 'Montemurlo', 'Cantagallo', 'Carmignano', 'Poggio a Caiano',
                            'Vaiano', 'Vernio',
                        ],
                    ],
                    [
                        'name' => 'Siena',
                        'code' => 'SI',
                        'cities' => [
                            'Siena', 'Poggibonsi', 'Colle di Val d\'Elsa', 'Montepulciano', 'Chiusi',
                            'Asciano', 'Buonconvento', 'Casole d\'Elsa', 'Castellina in Chianti', 'Gaiole in Chianti',
                            'Montalcino', 'Monteriggioni', 'Monticiano', 'Murlo', 'Piancastagnaio',
                            'Pienza', 'Radda in Chianti', 'Radicofani', 'Radicondoli', 'Rapolano Terme',
                            'San Gimignano', 'San Quirico d\'Orcia', 'Sarteano', 'Sinalunga', 'Torrita di Siena',
                            'Trequanda',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 10. UMBRIA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Umbria',
                'code' => 'UMB',
                'provinces' => [
                    [
                        'name' => 'Perugia',
                        'code' => 'PG',
                        'cities' => [
                            'Perugia', 'Foligno', 'Spoleto', 'Assisi', 'Città di Castello',
                            'Gubbio', 'Orvieto (confine)', 'Todi', 'Umbertide', 'Cascia',
                            'Castiglione del Lago', 'Corciano', 'Deruta', 'Gualdo Tadino', 'Magione',
                            'Marsciano', 'Nocera Umbra', 'Norcia', 'Passignano sul Trasimeno', 'Panicale',
                            'San Giustino', 'Spello', 'Torgiano', 'Trevi',
                        ],
                    ],
                    [
                        'name' => 'Terni',
                        'code' => 'TR',
                        'cities' => [
                            'Terni', 'Narni', 'Orvieto', 'Amelia', 'Arrone',
                            'Avigliano Umbro', 'Calvi dell\'Umbria', 'Ferentillo', 'Giove', 'Guardea',
                            'Lugnano in Teverina', 'Montefranco', 'Otricoli', 'Penna in Teverina', 'Polino',
                            'Porano', 'Stroncone',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 11. MARCHE
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Marche',
                'code' => 'MAR',
                'provinces' => [
                    [
                        'name' => 'Ancona',
                        'code' => 'AN',
                        'cities' => [
                            'Ancona', 'Senigallia', 'Fabriano', 'Jesi', 'Falconara Marittima',
                            'Chiaravalle', 'Camerano', 'Castelfidardo', 'Filottrano', 'Loreto',
                            'Monte San Vito', 'Montemarciano', 'Offagna', 'Osimo', 'Polverigi',
                            'Porto Recanati', 'Recanati', 'Ripe', 'Serra de\' Conti', 'Staffolo',
                        ],
                    ],
                    [
                        'name' => 'Ascoli Piceno',
                        'code' => 'AP',
                        'cities' => [
                            'Ascoli Piceno', 'San Benedetto del Tronto', 'Acquaviva Picena', 'Castorano', 'Colli del Tronto',
                            'Grottammare', 'Massignano', 'Monteprandone', 'Offida', 'Ripatransone',
                            'Spinetoli',
                        ],
                    ],
                    [
                        'name' => 'Fermo',
                        'code' => 'FM',
                        'cities' => [
                            'Fermo', 'Porto San Giorgio', 'Civitanova Marche (confine)', 'Montegranaro', 'Montegiorgio',
                            'Porto Sant\'Elpidio', 'Sant\'Elpidio a Mare', 'Altidona', 'Campofilone', 'Lapedona',
                            'Magliano di Tenna', 'Monsampietro Morico', 'Monterubbiano', 'Pedaso', 'Servigliano',
                        ],
                    ],
                    [
                        'name' => 'Macerata',
                        'code' => 'MC',
                        'cities' => [
                            'Macerata', 'Civitanova Marche', 'Porto Recanati', 'Tolentino', 'Camerino',
                            'Cingoli', 'Corridonia', 'Monte San Giusto', 'Morrovalle', 'Potenza Picena',
                            'Recanati (confine)', 'San Severino Marche', 'Treia', 'Visso',
                        ],
                    ],
                    [
                        'name' => 'Pesaro e Urbino',
                        'code' => 'PU',
                        'cities' => [
                            'Pesaro', 'Urbino', 'Fano', 'Fossombrone', 'Cagli',
                            'Cartoceto', 'Gradara', 'Mondavio', 'Mondolfo', 'Novafeltria (confine)',
                            'Pergola', 'Sant\'Angelo in Vado', 'Sassocorvaro Auditore', 'Senigallia (confine)', 'Urbania',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 12. LAZIO
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Lazio',
                'code' => 'LAZ',
                'provinces' => [
                    [
                        'name' => 'Roma',
                        'code' => 'RM',
                        'cities' => [
                            'Roma', 'Fiumicino', 'Guidonia Montecelio', 'Pomezia', 'Tivoli',
                            'Velletri', 'Aprilia (confine)', 'Anzio', 'Ardea', 'Bracciano',
                            'Castelli Romani', 'Ciampino', 'Civitavecchia', 'Colleferro', 'Frascati',
                            'Genzano di Roma', 'Grottaferrata', 'Ladispoli', 'Lanuvio', 'Lariano',
                            'Marino', 'Monte Compatri', 'Monte Porzio Catone', 'Monterotondo', 'Morlupo',
                            'Nettuno', 'Palestrina', 'Poli', 'Rocca di Papa', 'Rocca Priora',
                            'Sacrofano', 'San Cesareo', 'Santa Marinella', 'Segni', 'Subiaco',
                            'Trevignano Romano', 'Velletri', 'Viterbo (confine)', 'Zagarolo', 'Albano Laziale',
                            'Ariccia', 'Castel Gandolfo', 'Cave', 'Cerveteri', 'Colonna',
                            'Formello', 'Gallicano nel Lazio', 'Genazzano', 'Labico', 'Montecompatri',
                        ],
                    ],
                    [
                        'name' => 'Frosinone',
                        'code' => 'FR',
                        'cities' => [
                            'Frosinone', 'Cassino', 'Sora', 'Anagni', 'Alatri',
                            'Ferentino', 'Fiuggi', 'Pontecorvo', 'Veroli', 'Ceccano',
                            'Ceprano', 'Isola del Liri', 'Monte San Giovanni Campano', 'Paliano', 'Patrica',
                        ],
                    ],
                    [
                        'name' => 'Latina',
                        'code' => 'LT',
                        'cities' => [
                            'Latina', 'Aprilia', 'Terracina', 'Fondi', 'Formia',
                            'Cisterna di Latina', 'Gaeta', 'Minturno', 'Pontinia', 'Priverno',
                            'Sabaudia', 'San Felice Circeo', 'Sezze', 'Sonnino', 'Spigno Saturnia',
                        ],
                    ],
                    [
                        'name' => 'Rieti',
                        'code' => 'RI',
                        'cities' => [
                            'Rieti', 'Fara in Sabina', 'Poggio Mirteto', 'Antrodoco', 'Borgorose',
                            'Cantalice', 'Cittaducale', 'Magliano Sabina', 'Montopoli di Sabina', 'Passo Corese',
                        ],
                    ],
                    [
                        'name' => 'Viterbo',
                        'code' => 'VT',
                        'cities' => [
                            'Viterbo', 'Tarquinia', 'Civita Castellana', 'Montefiascone', 'Orte',
                            'Acquapendente', 'Bolsena', 'Capranica', 'Caprarola', 'Vetralla',
                            'Tuscania', 'Valentano', 'Nepi', 'Ronciglione',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 13. ABRUZZO
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Abruzzo',
                'code' => 'ABR',
                'provinces' => [
                    [
                        'name' => "L'Aquila",
                        'code' => 'AQ',
                        'cities' => [
                            "L'Aquila", 'Avezzano', 'Sulmona', 'Carsoli', 'Celano',
                            'Montesilvano (confine)', 'Pescara (confine)', 'Pescina', 'Tagliacozzo', 'Trasacco',
                            'Castel di Sangro', 'Gioia dei Marsi', 'Ovindoli',
                        ],
                    ],
                    [
                        'name' => 'Chieti',
                        'code' => 'CH',
                        'cities' => [
                            'Chieti', 'Lanciano', 'Vasto', 'Ortona', 'San Salvo',
                            'Atessa', 'Bucchianico', 'Casoli', 'Fossacesia', 'Francavilla al Mare',
                            'Guardiagrele', 'Orsogna', 'Paglieta', 'Pescara (confine)', 'Torino di Sangro',
                        ],
                    ],
                    [
                        'name' => 'Pescara',
                        'code' => 'PE',
                        'cities' => [
                            'Pescara', 'Montesilvano', 'Spoltore', 'Penne', 'Pianella',
                            'Cepagatti', 'Città Sant\'Angelo', 'Loreto Aprutino', 'Manoppello', 'Moscufo',
                            'Nocciano', 'Popoli', 'Rosciano',
                        ],
                    ],
                    [
                        'name' => 'Teramo',
                        'code' => 'TE',
                        'cities' => [
                            'Teramo', 'Giulianova', 'Roseto degli Abruzzi', 'Alba Adriatica', 'Silvi',
                            'Atri', 'Bellante', 'Bisenti', 'Campli', 'Castellalto',
                            'Corropoli', 'Martinsicuro', 'Nereto', 'Notaresco', 'Sant\'Egidio alla Vibrata',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 14. MOLISE
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Molise',
                'code' => 'MOL',
                'provinces' => [
                    [
                        'name' => 'Campobasso',
                        'code' => 'CB',
                        'cities' => [
                            'Campobasso', 'Termoli', 'Bojano', 'Larino', 'Riccia',
                            'Sepino', 'Vinchiaturo', 'Busso', 'Campodipietra', 'Ferrazzano',
                        ],
                    ],
                    [
                        'name' => 'Isernia',
                        'code' => 'IS',
                        'cities' => [
                            'Isernia', 'Venafro', 'Agnone', 'Frosolone', 'Pozzilli',
                            'Sant\'Agapito', 'Carpinone',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 15. CAMPANIA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Campania',
                'code' => 'CAM',
                'provinces' => [
                    [
                        'name' => 'Napoli',
                        'code' => 'NA',
                        'cities' => [
                            'Napoli', 'Giugliano in Campania', 'Torre del Greco', 'Pozzuoli', 'Casoria',
                            'Afragola', 'Castellammare di Stabia', 'Portici', 'Ercolano', 'Marano di Napoli',
                            'Acerra', 'Arzano', 'Aversa (confine)', 'Bacoli', 'Boscoreale',
                            'Caivano', 'Casavatore', 'Casalnuovo di Napoli', 'Cimitile', 'Cicciano',
                            'Frattamaggiore', 'Gragnano', 'Ischia', 'Marano', 'Marigliano',
                            'Mugnano di Napoli', 'Nola', 'Ottaviano', 'Palma Campania', 'Pompei',
                            'Pomigliano d\'Arco', 'Qualiano', 'Quarto', 'San Giorgio a Cremano', 'San Giuseppe Vesuviano',
                            'San Sebastiano al Vesuvio', 'Sant\'Antimo', 'Santa Maria Capua Vetere (confine)', 'Scafati', 'Sorrento',
                            'Torre Annunziata', 'Trecase', 'Villaricca', 'Volla',
                        ],
                    ],
                    [
                        'name' => 'Avellino',
                        'code' => 'AV',
                        'cities' => [
                            'Avellino', 'Ariano Irpino', 'Solofra', 'Montoro', 'Cervinara',
                            'Atripalda', 'Baiano', 'Grottaminarda', 'Lauro', 'Mercogliano',
                            'Monteforte Irpino', 'Nusco', 'Salza Irpina', 'Serino',
                        ],
                    ],
                    [
                        'name' => 'Benevento',
                        'code' => 'BN',
                        'cities' => [
                            'Benevento', 'Montesarchio', 'Sant\'Agata de\' Goti', 'Airola', 'Calvi',
                            'Ceppaloni', 'Dugenta', 'Foglianise', 'Paupisi', 'San Giorgio del Sannio',
                        ],
                    ],
                    [
                        'name' => 'Caserta',
                        'code' => 'CE',
                        'cities' => [
                            'Caserta', 'Aversa', 'Marcianise', 'Maddaloni', 'Santa Maria Capua Vetere',
                            'Capua', 'Casal di Principe', 'Caserta (San Leucio)', 'Gricignano di Aversa', 'Mondragone',
                            'Recale', 'San Marco Evangelista', 'San Nicola la Strada', 'San Prisco', 'Succivo',
                            'Teano', 'Trentola-Ducenta', 'Villa di Briano', 'Vitulazio',
                        ],
                    ],
                    [
                        'name' => 'Salerno',
                        'code' => 'SA',
                        'cities' => [
                            'Salerno', 'Cava de\' Tirreni', 'Battipaglia', 'Eboli', 'Nocera Inferiore',
                            'Agropoli', 'Amalfi', 'Baronissi', 'Campagna', 'Capaccio Paestum',
                            'Castel San Giorgio', 'Fisciano', 'Mercato San Severino', 'Montecorvino Rovella', 'Nocera Superiore',
                            'Pagani', 'Pellezzano', 'Pontecagnano Faiano', 'Positano', 'Ravello',
                            'Roccapiemonte', 'San Marzano sul Sarno', 'Sarno', 'Siano', 'Vietri sul Mare',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 16. PUGLIA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Puglia',
                'code' => 'PUG',
                'provinces' => [
                    [
                        'name' => 'Bari',
                        'code' => 'BA',
                        'cities' => [
                            'Bari', 'Altamura', 'Bitonto', 'Molfetta', 'Taranto (confine)',
                            'Acquaviva delle Fonti', 'Adelfia', 'Alberobello', 'Andria (confine)', 'Barletta (confine)',
                            'Binetto', 'Bitritto', 'Casamassima', 'Castellana Grotte', 'Conversano',
                            'Corato', 'Gioia del Colle', 'Giovinazzo', 'Gravina in Puglia', 'Grumo Appula',
                            'Locorotondo', 'Modugno', 'Monopoli', 'Noicattaro', 'Palo del Colle',
                            'Polignano a Mare', 'Putignano', 'Rutigliano', 'Ruvo di Puglia', 'Sammichele di Bari',
                            'Sannicandro di Bari', 'Santeramo in Colle', 'Triggiano', 'Valenzano',
                        ],
                    ],
                    [
                        'name' => 'Barletta-Andria-Trani',
                        'code' => 'BT',
                        'cities' => [
                            'Barletta', 'Andria', 'Trani', 'Bisceglie', 'Canosa di Puglia',
                            'Margherita di Savoia', 'Minervino Murge', 'San Ferdinando di Puglia', 'Spinazzola', 'Trinitapoli',
                        ],
                    ],
                    [
                        'name' => 'Brindisi',
                        'code' => 'BR',
                        'cities' => [
                            'Brindisi', 'Fasano', 'Francavilla Fontana', 'Mesagne', 'Ostuni',
                            'Carovigno', 'Cellino San Marco', 'Cisternino', 'Ceglie Messapica', 'Erchie',
                            'Latiano', 'Oria', 'San Donaci', 'San Michele Salentino', 'San Pancrazio Salentino',
                            'Torchiarolo', 'Torre Santa Susanna', 'Villa Castelli',
                        ],
                    ],
                    [
                        'name' => 'Foggia',
                        'code' => 'FG',
                        'cities' => [
                            'Foggia', 'Cerignola', 'Manfredonia', 'San Severo', 'Lucera',
                            'Bari (confine)', 'Bovino', 'Canosa di Puglia (confine)', 'Carapelle', 'Carpino',
                            'Deliceto', 'Ischitella', 'Lesina', 'Monte Sant\'Angelo', 'Orta Nova',
                            'Rodi Garganico', 'Rocchetta Sant\'Antonio', 'San Giovanni Rotondo', 'Torremaggiore', 'Vieste',
                        ],
                    ],
                    [
                        'name' => 'Lecce',
                        'code' => 'LE',
                        'cities' => [
                            'Lecce', 'Brindisi (confine)', 'Gallipoli', 'Nardò', 'Maglie',
                            'Copertino', 'Galatina', 'Galatone', 'Surbo', 'Squinzano',
                            'Aradeo', 'Calimera', 'Campi Salentina', 'Carmiano', 'Casarano',
                            'Cavallino', 'Cutrofiano', 'Leverano', 'Lizzanello', 'Matino',
                            'Melendugno', 'Melissano', 'Monteroni di Lecce', 'Morciano di Leuca', 'Otranto',
                            'Parabita', 'Porto Cesareo', 'Racale', 'Ruffano', 'Salve',
                            'San Donato di Lecce', 'Sanarica', 'Sannicola', 'Scorrano', 'Secli\'',
                            'Taviano', 'Taurisano', 'Tricase', 'Tuglie', 'Ugento',
                        ],
                    ],
                    [
                        'name' => 'Taranto',
                        'code' => 'TA',
                        'cities' => [
                            'Taranto', 'Massafra', 'Castellaneta', 'Grottaglie', 'Manduria',
                            'Martina Franca', 'Crispiano', 'Fragagnano', 'Ginosa', 'Laterza',
                            'Leporano', 'Lizzano', 'Monteiasi', 'Montemesola', 'Monteparano',
                            'Mottola', 'Palagianello', 'Palagiano', 'Pulsano', 'San Giorgio Ionico',
                            'Sava', 'Statte', 'Torricella',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 17. BASILICATA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Basilicata',
                'code' => 'BAS',
                'provinces' => [
                    [
                        'name' => 'Potenza',
                        'code' => 'PZ',
                        'cities' => [
                            'Potenza', 'Melfi', 'Lavello', 'Venosa', 'Policoro (confine)',
                            'Pisticci (confine)', 'Rionero in Vulture', 'Satriano di Lucania', 'Lauria', 'Lagonegro',
                            'Maratea', 'Muro Lucano', 'San Fele', 'Senise',
                        ],
                    ],
                    [
                        'name' => 'Matera',
                        'code' => 'MT',
                        'cities' => [
                            'Matera', 'Policoro', 'Pisticci', 'Bernalda', 'Nova Siri',
                            'Rotondella', 'Scanzano Ionico', 'Stigliano', 'Tursi',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 18. CALABRIA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Calabria',
                'code' => 'CAL',
                'provinces' => [
                    [
                        'name' => 'Catanzaro',
                        'code' => 'CZ',
                        'cities' => [
                            'Catanzaro', 'Lamezia Terme', 'Soverato', 'Sellia Marina', 'Settingiano',
                            'Borgia', 'Caraffa di Catanzaro', 'Cardinale', 'Cortale', 'Cropani',
                            'Fossato Serralta', 'Girifalco', 'Guardavalle', 'Jacurso', 'Magisano',
                            'Maida', 'Marcellinara', 'Miglierina', 'Montauro', 'Pentone',
                            'San Floro', 'Serrastretta', 'Squillace', 'Taverna',
                        ],
                    ],
                    [
                        'name' => 'Cosenza',
                        'code' => 'CS',
                        'cities' => [
                            'Cosenza', 'Rende', 'Castrovillari', 'Corigliano-Rossano', 'Scalea',
                            'Acri', 'Amantea', 'Cassano all\'Ionio', 'Cetraro', 'Diamante',
                            'Lungro', 'Montalto Uffugo', 'Paola', 'Praia a Mare', 'San Giovanni in Fiore',
                            'Trebisacce', 'Altomonte', 'Bisignano', 'Cariati', 'Carolei',
                            'Fuscaldo', 'Luzzi', 'Mangone', 'Mendicino', 'Roggiano Gravina',
                            'Rose', 'Rossano', 'San Demetrio Corone', 'Spezzano Albanese', 'Tarsia',
                        ],
                    ],
                    [
                        'name' => 'Crotone',
                        'code' => 'KR',
                        'cities' => [
                            'Crotone', 'Cirò Marina', 'Isola di Capo Rizzuto', 'Mesoraca', 'Petilia Policastro',
                            'Rocca di Neto', 'San Giovanni e Paolo', 'Scandale', 'Strongoli',
                        ],
                    ],
                    [
                        'name' => 'Reggio Calabria',
                        'code' => 'RC',
                        'cities' => [
                            'Reggio Calabria', 'Gioia Tauro', 'Locri', 'Palmi', 'Villa San Giovanni',
                            'Bagnara Calabra', 'Bianco', 'Cinquefrondi', 'Cittanova', 'Gioiosa Ionica',
                            'Melito di Porto Salvo', 'Oppido Mamertina', 'Polistena', 'Rosarno', 'Sant\'Eufemia d\'Aspromonte',
                            'Scilla', 'Siderno', 'Sinopoli', 'Taurianova',
                        ],
                    ],
                    [
                        'name' => 'Vibo Valentia',
                        'code' => 'VV',
                        'cities' => [
                            'Vibo Valentia', 'Tropea', 'Serra San Bruno', 'Filadelfia', 'Pizzo',
                            'Briatico', 'Cessaniti', 'Filandari', 'Ionadi', 'Joppolo',
                            'Limbadi', 'Mileto', 'Nicotera', 'Parghelia', 'Ricadi',
                            'Rombiolo', 'Zambrone', 'Zungri',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 19. SICILIA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Sicilia',
                'code' => 'SIC',
                'provinces' => [
                    [
                        'name' => 'Palermo',
                        'code' => 'PA',
                        'cities' => [
                            'Palermo', 'Bagheria', 'Carini', 'Monreale', 'Misilmeri',
                            'Alcamo', 'Balestrate', 'Bolognetta', 'Borgetto', 'Caccamo',
                            'Capaci', 'Castelbuono', 'Casteldaccia', 'Cefalù', 'Cinisi',
                            'Corleone', 'Ficarazzi', 'Giardinello', 'Isola delle Femmine', 'Lercara Friddi',
                            'Marineo', 'Partinico', 'Petralia Soprana', 'Petralia Sottana', 'Piana degli Albanesi',
                            'Santa Flavia', 'Sciara', 'Termini Imerese', 'Terrasini', 'Torretta',
                            'Trabia', 'Trabia', 'Trappeto', 'Villabate', 'Villafrati',
                        ],
                    ],
                    [
                        'name' => 'Catania',
                        'code' => 'CT',
                        'cities' => [
                            'Catania', 'Acireale', 'Misterbianco', 'Paternò', 'Gravina di Catania',
                            'San Giovanni la Punta', 'Mascalucia', 'Belpasso', 'Caltagirone', 'Giarre',
                            'Adrano', 'Biancavilla', 'Calatabiano', 'Camporotondo Etneo', 'Castel di Iudica',
                            'Fiumefreddo di Sicilia', 'Licodia Eubea', 'Linguaglossa', 'Militello in Val di Catania', 'Milo',
                            'Mineo', 'Nicolosi', 'Pedara', 'Randazzo', 'Riposto',
                            'San Gregorio di Catania', 'San Pietro Clarenza', 'Sant\'Agata li Battiati', 'Scordia', 'Trecastagni',
                            'Tremestieri Etneo', 'Valverde', 'Viagrande', 'Zafferana Etnea',
                        ],
                    ],
                    [
                        'name' => 'Messina',
                        'code' => 'ME',
                        'cities' => [
                            'Messina', 'Barcellona Pozzo di Gotto', 'Milazzo', 'Patti', 'Sant\'Agata di Militello',
                            'Capo d\'Orlando', 'Letojanni', 'Lipari', 'Milazzo', 'Mistretta',
                            'Nizza di Sicilia', 'Pace del Mela', 'Raccuja', 'Randazzo (confine)', 'Santa Lucia del Mela',
                            'Santo Stefano di Camastra', 'Taormina', 'Terme Vigliatore', 'Venetico',
                        ],
                    ],
                    [
                        'name' => 'Agrigento',
                        'code' => 'AG',
                        'cities' => [
                            'Agrigento', 'Sciacca', 'Licata', 'Canicattì', 'Favara',
                            'Aragona', 'Bivona', 'Cammarata', 'Campobello di Licata', 'Casteltermini',
                            'Cattolica Eraclea', 'Comitini', 'Grotte', 'Joppolo Giancaxio', 'Menfi',
                            'Montallegro', 'Montevago', 'Naro', 'Palma di Montechiaro', 'Porto Empedocle',
                            'Raffadali', 'Ravanusa', 'Realmonte', 'Ribera', 'Sambuca di Sicilia',
                            'San Biagio Platani', 'Santa Margherita di Belice', 'Siculiana',
                        ],
                    ],
                    [
                        'name' => 'Caltanissetta',
                        'code' => 'CL',
                        'cities' => [
                            'Caltanissetta', 'Gela', 'Niscemi', 'San Cataldo', 'Mussomeli',
                            'Butera', 'Campofranco', 'Delia', 'Marianopoli', 'Milena',
                            'Montedoro', 'Mazzarino', 'Resuttano', 'Riesi', 'Serradifalco',
                            'Sommatino', 'Sutera', 'Villalba',
                        ],
                    ],
                    [
                        'name' => 'Enna',
                        'code' => 'EN',
                        'cities' => [
                            'Enna', 'Piazza Armerina', 'Leonforte', 'Nicosia', 'Agira',
                            'Aidone', 'Assoro', 'Barrafranca', 'Calascibetta', 'Catenanuova',
                            'Cerami', 'Gagliano Castelferrato', 'Nissoria', 'Pietraperzia', 'Regalbuto',
                            'Sperlinga', 'Troina', 'Valguarnera Caropepe', 'Villarosa',
                        ],
                    ],
                    [
                        'name' => 'Ragusa',
                        'code' => 'RG',
                        'cities' => [
                            'Ragusa', 'Modica', 'Vittoria', 'Comiso', 'Scicli',
                            'Acate', 'Chiaramonte Gulfi', 'Giarratana', 'Ispica', 'Marina di Ragusa',
                            'Monterosso Almo', 'Pozzallo', 'Santa Croce Camerina',
                        ],
                    ],
                    [
                        'name' => 'Siracusa',
                        'code' => 'SR',
                        'cities' => [
                            'Siracusa', 'Augusta', 'Avola', 'Noto', 'Pachino',
                            'Lentini', 'Floridia', 'Francofonte', 'Melilli', 'Palazzolo Acreide',
                            'Portopalo di Capo Passero', 'Priolo Gargallo', 'Rosolini', 'Solarino', 'Sortino',
                        ],
                    ],
                    [
                        'name' => 'Trapani',
                        'code' => 'TP',
                        'cities' => [
                            'Trapani', 'Marsala', 'Alcamo (confine)', 'Erice', 'Castelvetrano',
                            'Mazara del Vallo', 'Paceco', 'Campobello di Mazara', 'Calatafimi-Segesta', 'Custonaci',
                            'Gibellina', 'Misiliscemi', 'Petrosino', 'Poggioreale', 'Salaparuta',
                            'Salemi', 'Santa Ninfa', 'Valderice', 'Vita',
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // 20. SARDEGNA
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Sardegna',
                'code' => 'SAR',
                'provinces' => [
                    [
                        'name' => 'Cagliari',
                        'code' => 'CA',
                        'cities' => [
                            'Cagliari', 'Quartu Sant\'Elena', 'Selargius', 'Monserrato', 'Quartucciu',
                            'Assemini', 'Capoterra', 'Decimomannu', 'Elmas', 'Maracalagonis',
                            'Pula', 'Sarroch', 'Sestu', 'Settimo San Pietro', 'Sinnai',
                            'Soleminis', 'Uta', 'Villa San Pietro',
                        ],
                    ],
                    [
                        'name' => 'Sassari',
                        'code' => 'SS',
                        'cities' => [
                            'Sassari', 'Alghero', 'Porto Torres', 'Sorso', 'Sennori',
                            'Castelsardo', 'Golfo Aranci', 'Ittiri', 'La Maddalena', 'Olbia (confine)',
                            'Ossi', 'Palau', 'Platamona', 'Santa Teresa Gallura', 'Stintino',
                            'Tempio Pausania (confine)', 'Trinità d\'Agultu e Vignola', 'Uri', 'Usini',
                        ],
                    ],
                    [
                        'name' => 'Nuoro',
                        'code' => 'NU',
                        'cities' => [
                            'Nuoro', 'Macomer', 'Siniscola', 'Olbia (confine)', 'Bosa',
                            'Dorgali', 'Fonni', 'Gavoi', 'Orgosolo', 'Orosei',
                            'Ottana', 'Sorgono', 'Tonara',
                        ],
                    ],
                    [
                        'name' => 'Oristano',
                        'code' => 'OR',
                        'cities' => [
                            'Oristano', 'Cabras', 'Terralba', 'Ales', 'Ghilarza',
                            'Laconi', 'Mogoro', 'Paulilatino', 'Santa Giusta', 'Solarussa',
                        ],
                    ],
                    [
                        'name' => 'Sud Sardegna',
                        'code' => 'SU',
                        'cities' => [
                            'Carbonia', 'Iglesias', 'Sant\'Antioco', 'Portoscuso', 'Villamassargia',
                            'Domusnovas', 'Giba', 'Musei', 'Narcao', 'San Giovanni Suergiu',
                            'Santadi', 'Serdiana', 'Siliqua', 'Teulada', 'Villaperuccio',
                            'Sanluri', 'Guspini', 'Villacidro', 'Serramanna', 'Decimomannu (confine)',
                            'Samassi', 'Serrenti',
                        ],
                    ],
                    [
                        'name' => 'Olbia-Tempio',
                        'code' => 'OT',
                        'cities' => [
                            'Olbia', 'Tempio Pausania', 'Arzachena', 'San Teodoro', 'Budoni',
                            'Calangianus', 'La Maddalena', 'Loiri Porto San Paolo', 'Luras', 'Monti',
                            'Padria', 'Palau', 'Porto Cervo', 'Santa Teresa Gallura', 'Telti',
                        ],
                    ],
                    [
                        'name' => 'Medio Campidano',
                        'code' => 'VS',
                        'cities' => [
                            'Sanluri', 'Villacidro', 'Guspini', 'Gonnosfanadiga', 'Sardara',
                            'Barumini', 'Gesturi', 'Lunamatrona', 'Pabillonis', 'Samassi',
                            'San Gavino Monreale', 'Serri', 'Serrenti', 'Tuili', 'Turri',
                            'Ussaramanna', 'Villamar',
                        ],
                    ],
                    [
                        'name' => 'Ogliastra',
                        'code' => 'OG',
                        'cities' => [
                            'Lanusei', 'Tortolì', 'Bari Sardo', 'Cardedu', 'Elini',
                            'Gairo', 'Girasole', 'Ilbono', 'Jerzu', 'Lotzorai',
                            'Osini', 'Perdasdefogu', 'Seui', 'Talana', 'Triei',
                            'Ulassai', 'Urzulei', 'Ussassai', 'Villagrande Strisaili',
                        ],
                    ],
                ],
            ],
        ];
    }
}
