<?php
    // =====================================================
    // SPECIMEN
    // =====================================================

    return [
        'label' => 'Specimen',
        'icon' => '🧪',
        'description' => 'Specimen resources for lab tests and diagnostics',
        'endpoints' => [
            [
                'key' => 'search_specimen_by_patient_and_collector',
                'label' => 'Search Specimen by Patient & Collector',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen?subject={patient_id}&collector={collector_id}',
                'description' => 'Mencari data sampel (Specimen) berdasarkan ID Pasien (subject) dan ID Tenaga Kesehatan pengambil sampel (collector). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'collector_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Practitioner ID Nakes (cth: N10000001)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_specimen_by_patient_and_collected_date',
                'label' => 'Search Specimen by Patient & Collected Date',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen?subject={patient_id}&collected={collected_date}',
                'description' => 'Mencari data sampel berdasarkan ID Pasien (subject) dan tanggal pengambilan sampel (collected). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'collected_date',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD (cth: 2022-06-28)',
                        'default' => ''
                    ]
                ],
            ],
        
            // ==========================================================
            // 2. SPECIMEN - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_specimen',
                'label' => 'Create Specimen',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Specimen',
                'description' => 'Mendaftarkan data pengambilan sampel baru pasien untuk keperluan pemeriksaan laboratorium ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Specimen',
                    'status' => 'available', // Status sampel: available | unavailable | unsatisfactory | entered-in-error
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '119297000', // Contoh kode SNOMED CT untuk spesimen darah lengkap (Blood specimen)
                                'display' => 'Blood specimen'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'request' => [
                        [
                            'reference' => 'ServiceRequest/{servicerequest_id}' // Merujuk ke ID ServiceRequest (Order Permintaan Lab)
                        ]
                    ],
                    'collection' => [
                        'collector' => [
                            'reference' => 'Practitioner/{practitioner_id}', // ID Tenaga Kesehatan / Analis yang mengambil sampel
                            'display' => 'Nama Analis Laboratorium'
                        ],
                        'collectedDateTime' => '2026-05-29T10:00:00+07:00', // Waktu pengambilan sampel dilakukan
                        'quantity' => [
                            'value' => 3,
                            'unit' => 'mL',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'mL'
                        ],
                        'bodySite' => [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '49852007', // Contoh kode SNOMED CT lokasi tubuh (cth: Median cubital vein / Vena fossa cubiti)
                                    'display' => 'Structure of median cubital vein'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'get_specimen',
                'label' => 'Get Specimen by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen/{id}',
                'description' => 'Get specimen by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. SPECIMEN - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_specimen',
                'label' => 'Search Specimen',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen?subject={patient_id}',
                'description' => 'Mencari data rekam riwayat pengambilan sampel klinis (Specimen) berdasarkan ID Pasien (subject).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. SPECIMEN - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_specimen',
                'label' => 'Update Specimen',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Specimen/{id}',
                'description' => 'Memperbarui rekam data spesimen secara keseluruhan berdasarkan ID Specimen. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Specimen',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'available',
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '119297000',
                                'display' => 'Blood specimen'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'request' => [
                        [
                            'reference' => 'ServiceRequest/{servicerequest_id}'
                        ]
                    ],
                    'collection' => [
                        'collector' => [
                            'reference' => 'Practitioner/{practitioner_id}',
                            'display' => 'Nama Analis Laboratorium'
                        ],
                        'collectedDateTime' => '2026-05-29T10:00:00+07:00',
                        'quantity' => [
                            'value' => 5, // Contoh pembaruan data: Penyesuaian volume spesimen menjadi 5 mL
                            'unit' => 'mL',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'mL'
                        ],
                        'bodySite' => [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '49852007',
                                    'display' => 'Structure of median cubital vein'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. SPECIMEN - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_specimen',
                'label' => 'Patch Specimen',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Specimen/{id}',
                'description' => 'Memperbarui elemen informasi data sampel biologis secara parsial (sebagian) menggunakan format array JSON Patch (misalnya merubah status ketersediaan sampel dengan cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status spesimen
                        'value' => 'unsatisfactory' // Contoh perubahan status parsial: Sampel rusak / lisis sehingga tidak layak uji lab
                    ]
                ],
            ],

            // [
            //     'key' => 'delete_specimen',
            //     'label' => 'Delete Specimen',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/Specimen/{id}',
            //     'description' => 'Delete specimen',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Specimen ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_specimen',
                'label' => 'History Specimen',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen/{id}/_history',
                'description' => 'Get specimen history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'patch_specimen',
                'label' => 'Patch Specimen',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Specimen/{id}',
                'description' => 'Patch specimen',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_specimen',
            //     'label' => 'History Type Specimen',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Specimen/_history',
            //     'description' => 'Get all specimen history',
            //     'params' => [],
            // ],
        ]
    ];