<?php    
    // =====================================================
    // ALLERGY INTOLERANCE
    // =====================================================

    return [
        'label' => 'AllergyIntolerance',
        'icon' => '⚠️',
        'description' => 'Allergy resources',
        'endpoints' => [

            // [
            //     'key' => 'create_allergyintolerance',
            //     'label' => 'Create AllergyIntolerance',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/AllergyIntolerance',
            //     'description' => 'Create allergy',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'AllergyIntolerance',

            //         'clinicalStatus' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
            //                     'code' => 'active'
            //                 ]
            //             ]
            //         ],

            //         'verificationStatus' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
            //                     'code' => 'confirmed'
            //                 ]
            //             ]
            //         ],

            //         'code' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://snomed.info/sct',
            //                     'code' => '',
            //                     'display' => ''
            //                 ]
            //             ]
            //         ],

            //         'patient' => [
            //             'reference' => 'Patient/{patient_id}'
            //         ]
            //     ]
            // ],

            // ==========================================================
            // 1. ALLERGY INTOLERANCE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_allergy_intolerance',
                'label' => 'Search AllergyIntolerance',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/AllergyIntolerance?patient={patient_id}',
                'description' => 'Mencari rekam medis riwayat alergi pasien (AllergyIntolerance) berdasarkan ID Pasien (patient).',
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
            // 2. ALLERGY INTOLERANCE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_allergy_intolerance',
                'label' => 'Create AllergyIntolerance',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/AllergyIntolerance',
                'description' => 'Mendaftarkan data riwayat alergi atau intoleransi baru pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'AllergyIntolerance',
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
                                'code' => 'active', // Status klinis: active | inactive | resolved
                                'display' => 'Active'
                            ]
                        ]
                    ],
                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
                                'code' => 'confirmed', // Status verifikasi: unconfirmed | confirmed | refuted | entered-in-error
                                'display' => 'Confirmed'
                            ]
                        ]
                    ],
                    'category' => [
                        'medication' // Kategori alergi: medication | food | biologic | environment | biocompatibility
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '764146007', // Contoh kode SNOMED CT untuk Alergi Obat Amoxicillin
                                'display' => 'Allergy to amoxicillin'
                            ]
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/{patient_id}', // Ganti dengan ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}' // Mengikat ID Encounter kunjungan pasien
                    ],
                    'recordedDate' => '2026-05-29T10:00:00+07:00', // Waktu riwayat alergi ini dicatat
                    'recorder' => [
                        'reference' => 'Practitioner/{practitioner_id}' // ID Tenaga Kesehatan / Dokter yang mencatat riwayat alergi
                    ]
                ]
            ],

            // ==========================================================
            // 3. ALLERGY INTOLERANCE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_allergy_intolerance',
                'label' => 'Update AllergyIntolerance',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/AllergyIntolerance/{id}',
                'description' => 'Memperbarui data riwayat alergi secara keseluruhan berdasarkan ID AllergyIntolerance. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'AllergyIntolerance ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'AllergyIntolerance',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
                                'code' => 'resolved', // Skenario pembaruan: diubah menjadi resolved karena alergi pasien dinyatakan sudah sembuh/hilang
                                'display' => 'Resolved'
                            ]
                        ]
                    ],
                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
                                'code' => 'confirmed',
                                'display' => 'Confirmed'
                            ]
                        ]
                    ],
                    'category' => [
                        'medication'
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '764146007',
                                'display' => 'Allergy to amoxicillin'
                            ]
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ],
                    'recordedDate' => '2026-05-29T10:00:00+07:00',
                    'recorder' => [
                        'reference' => 'Practitioner/{practitioner_id}'
                    ]
                ]
            ],

            // ==========================================================
            // 4. ALLERGY INTOLERANCE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_allergy_intolerance',
                'label' => 'Patch AllergyIntolerance',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/AllergyIntolerance/{id}',
                'description' => 'Memperbarui data elemen riwayat alergi secara parsial (sebagian) menggunakan format array JSON Patch (sangat berguna untuk merubah status klinis secara cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'AllergyIntolerance ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',                                         // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/clinicalStatus/coding/0/code',                 // Target elemen kode status klinis
                        'value' => 'inactive'                                      // Nilai baru, diubah menjadi tidak aktif
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/clinicalStatus/coding/0/display',              // Target teks tampilan status klinis
                        'value' => 'Inactive'
                    ]
                ],
            ],

        ]
    ];