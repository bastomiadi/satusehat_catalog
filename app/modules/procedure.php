<?php
    // =====================================================
    // PROCEDURE
    // =====================================================

    return [
        'label' => 'Procedure',
        'icon' => '🔪',
        'description' => 'Procedure records and surgical interventions',
        'endpoints' => [

            // [
            //     'key' => 'create_procedure',
            //     'label' => 'Create Procedure',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Procedure',
            //     'description' => 'Create procedure',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Procedure',
            //         'status' => 'completed',
            //         'code' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
            //                     'code' => '',
            //                     'display' => ''
            //                 ]
            //             ]
            //         ],
            //         'subject' => [
            //             'reference' => 'Patient/{patient_id}'
            //         ]
            //     ]
            // ],

            [
                'key' => 'get_procedure',
                'label' => 'Get Procedure by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure/{id}',
                'description' => 'Get procedure by SATUSEHAT ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Procedure ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_procedure',
                'label' => 'Search Procedure by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure?subject={patient_id}',
                'description' => 'Search procedures for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_procedure_by_encounter',
                'label' => 'Search Procedure by Encounter ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure?encounter={encounter_id}',
                'description' => 'Mencari semua data tindakan medis (Procedure) yang dilakukan selama satu kunjungan (Encounter) tertentu.',
                'params' => [
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (contoh: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'update_procedure',
            //     'label' => 'Update Procedure',
            //     'method' => 'PUT',
            //     'path' => '/fhir-r4/v1/Procedure/{id}',
            //     'description' => 'Update procedure',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Procedure ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            // [
            //     'key' => 'patch_procedure',
            //     'label' => 'Patch Procedure',
            //     'method' => 'PATCH',
            //     'path' => '/fhir-r4/v1/Procedure/{id}',
            //     'description' => 'Patch procedure',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Procedure ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            // [
            //     'key' => 'delete_procedure',
            //     'label' => 'Delete Procedure',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/Procedure/{id}',
            //     'description' => 'Delete procedure',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Procedure ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_procedure',
                'label' => 'History Procedure',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure/{id}/_history',
                'description' => 'Get procedure history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Procedure ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. PROCEDURE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_procedure_by_patiend_id_and_encounter_id',
                'label' => 'Search Procedure by Patiend Id and Encounter Id',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari rekam medis tindakan/prosedur (Procedure) berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Kunjungan (cth: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. PROCEDURE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_procedure',
                'label' => 'Create Procedure',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Procedure',
                'description' => 'Mendaftarkan data rekam medis tindakan atau prosedur klinis baru yang telah dilakukan kepada pasien.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Procedure',
                    'status' => 'completed', // Status tindakan: preparation | in-progress | not-done | on-hold | completed | entered-in-error | unknown
                    'category' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '409073007', // Contoh kode kategori SNOMED CT: Surgical procedure
                                'display' => 'Surgical procedure'
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
                                'code' => '89.52', // Contoh tindakan berdasarkan ICD-9-CM (cth: Electrocardiogram / EKG)
                                'display' => 'Electrocardiogram'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f', // Mengikat ID Encounter kunjungan terkait
                        'display' => 'Kunjungan Rawat Jalan/Inap Terkait'
                    ],
                    'performedPeriod' => [
                        'start' => '2026-05-29T10:00:00+07:00', // Waktu tindakan dimulai
                        'end' => '2026-05-29T10:30:00+07:00'    // Waktu tindakan selesai
                    ],
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/N10000001', // ID Dokter DPJP / Nakes yang melakukan tindakan
                                'display' => 'Nama Dokter Pelaksana Tindakan'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'I21.9', // Alasan medis dilakukan tindakan (ICD-10)
                                    'display' => 'Acute myocardial infarction, unspecified'
                                ]
                            ]
                        ]
                    ],
                    'note' => [
                        [
                            'text' => 'Hasil perekaman EKG menunjukkan adanya ST-elevasi ringan, tindakan berjalan lancar tanpa komplikasi.' // Catatan klinis tambahan pelaksana tindakan
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. PROCEDURE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_procedure',
                'label' => 'Update Procedure',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Procedure/{id}',
                'description' => 'Memperbarui dokumen rekaman tindakan medis secara keseluruhan berdasarkan ID Procedure. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Procedure ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Procedure',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'completed',
                    'category' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '409073007',
                                'display' => 'Surgical procedure'
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
                                'code' => '89.52',
                                'display' => 'Electrocardiogram (Data Terupdate)'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f',
                        'display' => 'Kunjungan Rawat Jalan/Inap Terkait'
                    ],
                    'performedPeriod' => [
                        'start' => '2026-05-29T10:00:00+07:00',
                        'end' => '2026-05-29T10:30:00+07:00'
                    ],
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/N10000001',
                                'display' => 'Nama Dokter Pelaksana Tindakan'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'I21.9',
                                    'display' => 'Acute myocardial infarction, unspecified'
                                ]
                            ]
                        ]
                    ],
                    'note' => [
                        [
                            'text' => 'Hasil perekaman EKG terverifikasi ulang oleh DPJP.'
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. PROCEDURE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_procedure',
                'label' => 'Patch Procedure',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Procedure/{id}',
                'description' => 'Memperbarui elemen atau status rekaman tindakan secara parsial menggunakan format spesifikasi array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Procedure ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi penggantian nilai
                        'path' => '/status',     // Jalur elemen target (misal mengubah status tindakan)
                        'value' => 'entered-in-error' // Mengubah status menjadi entered-in-error jika terjadi salah input tindakan pada SIMRS
                    ]
                ],
            ],

        ]
    ];