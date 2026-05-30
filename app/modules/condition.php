<?php
// =====================================================
    // CONDITION
    // =====================================================

    return [
        'label' => 'Condition',
        'icon' => '🤒',
        'description' => 'Condition and problem list',
        'endpoints' => [
            // ==========================================================
            // 2. CONDITION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_condition',
                'label' => 'Create Condition',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Condition',
                'description' => 'Mencatatkan data diagnosis penyakit atau keluhan klinis baru pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Condition',
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                                'code' => 'active', // active | recurrence | relapse | inactive | remission | resolved
                                'display' => 'Active'
                            ]
                        ]
                    ],
                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                                'code' => 'confirmed', // unconfirmed | provisional | differential | confirmed | refuted | entered-in-error
                                'display' => 'Confirmed'
                            ]
                        ]
                    ],
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                                    'code' => 'encounter-diagnosis', // encounter-diagnosis = Diagnosis Kunjungan, problem-list-item = Riwayat Masalah
                                    'display' => 'Encounter Diagnosis'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-10',
                                'code' => 'K35.8', // Kode ICD-10 (contoh: Acute appendicitis, other and unspecified)
                                'display' => 'Acute appendicitis, other and unspecified'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}', // Mengikat ID Encounter kunjungan terkait
                        'display' => 'Kunjungan Terkait Diagnosis Ini'
                    ],
                    'onsetDateTime' => '2026-05-29T08:00:00+07:00', // Waktu pertama kali gejala/kondisi dirasakan
                    'recordedDate' => '2026-05-29T10:15:00+07:00' // Waktu saat diagnosis dicatat di sistem
                ]
            ],
            [
                'key' => 'get_condition_id',
                'label' => 'Get Condition by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Condition/{id}',
                'description' => 'Mengambil data kondisi penyakit berdasarkan ID.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Condition SATUSEHAT', 'default' => '']
                ]
            ],
            // [
            //     'key' => 'create_condition',
            //     'label' => 'Create Condition',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Condition',
            //     'description' => 'Create condition',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Condition',
            //         'clinicalStatus' => 'active',
            //         'verificationStatus' => 'confirmed',
            //         'category' => [
            //             [
            //                 'coding' => [
            //                     [
            //                         'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
            //                         'code' => 'problem-list-item'
            //                     ]
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
            //         'subject' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],
            // [
            //     'key' => 'get_condition',
            //     'label' => 'Get Condition by ID',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Condition/{id}',
            //     'description' => 'Get condition by ID',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Condition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            [
                'key' => 'search_condition',
                'label' => 'Search Condition by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Condition?subject={patient_id}',
                'description' => 'Search conditions for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. CONDITION - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_condition_by_patiend_id_and_encounter_id',
                'label' => 'Search Condition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Condition?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari data diagnosis atau kondisi klinis pasien berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
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
                        'placeholder' => 'Format: UUID Kunjungan (cth: 4f735a03-128b-464d-bf91-e7eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. CONDITION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_condition',
                'label' => 'Update Condition',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Condition/{id}',
                'description' => 'Memperbarui dokumen diagnosis secara keseluruhan berdasarkan ID Condition. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Condition ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Condition',
                    'id' => '{id}', // WAJIB disertakan pada metode PUT dan harus sama dengan parameter URL
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                                'code' => 'resolved', // Skenario pembaruan: Kondisi penyakit diubah dari 'active' menjadi 'resolved' (sembuh)
                                'display' => 'Resolved'
                            ]
                        ]
                    ],
                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                                'code' => 'confirmed',
                                'display' => 'Confirmed'
                            ]
                        ]
                    ],
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                                    'code' => 'encounter-diagnosis',
                                    'display' => 'Encounter Diagnosis'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-10',
                                'code' => 'K35.8',
                                'display' => 'Acute appendicitis, other and unspecified'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ],
                    'onsetDateTime' => '2026-05-29T08:00:00+07:00',
                    'recordedDate' => '2026-05-29T10:15:00+07:00',
                    'abatementDateTime' => '2026-05-29T18:00:00+07:00' // Ditambahkan waktu kesembuhan/redanya penyakit
                ]
            ],

            // ==========================================================
            // 4. CONDITION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_condition',
                'label' => 'Patch Condition',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Condition/{id}',
                'description' => 'Memperbarui elemen informasi rekam diagnosis secara parsial (sebagian) menggunakan spesifikasi operasi array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Condition ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',
                        'path' => '/clinicalStatus/coding/0/code', // Mengubah nilai kode status klinis secara spesifik
                        'value' => 'inactive'
                    ]
                ],
            ],
        ]
    ];