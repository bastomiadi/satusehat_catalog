<?php    
    // =====================================================
    // QUESTIONNAIRE RESPONSE
    // =====================================================

    return [
        'label' => 'QuestionnaireResponse',
        'icon' => '❓',
        'description' => 'Questionnaire responses',
        'endpoints' => [
            // [
            //     'key' => 'create_questionnaireresponse',
            //     'label' => 'Create QuestionnaireResponse',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/QuestionnaireResponse',
            //     'description' => 'Create questionnaire response',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'QuestionnaireResponse',
            //         'status' => 'completed',
            //         'questionnaire' => ['reference' => 'Questionnaire/{questionnaire_id}'],
            //         'subject' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],

            // ==========================================================
            // 1. QUESTIONNAIRE RESPONSE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_questionnaire_response',
                'label' => 'Search QuestionnaireResponse',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/QuestionnaireResponse?patient={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari dokumen hasil kuesioner/asesmen (QuestionnaireResponse) berdasarkan ID Pasien (patient) dan ID Kunjungan (encounter). Kedua parameter ini WAJIB ada bersamaan.',
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
                        'placeholder' => 'Format: UUID Kunjungan (cth: bc5edf78-ea8d-4827-97b3-3c73a810fa29)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. QUESTIONNAIRE RESPONSE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_questionnaire_response',
                'label' => 'Create QuestionnaireResponse',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/QuestionnaireResponse',
                'description' => 'Mendaftarkan hasil jawaban formulir kuesioner/skrining baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'QuestionnaireResponse',
                    'questionnaire' => 'https://fhir.kemkes.go.id/Questionnaire/Q0001', // Tautan kuesioner referensi standar Kemenkes
                    'status' => 'completed', // Status dokumen: in-progress | completed | amended | entered-in-error
                    'subject' => [
                        'reference' => 'Patient/{patient_id}', // ID Pasien SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29' // Mengikat ID Encounter kunjungan
                    ],
                    'authored' => '2026-05-29T17:00:00+07:00', // Waktu pengisian kuesioner dilakukan
                    'author' => [
                        'reference' => 'Practitioner/N10000001' // ID Tenaga Kesehatan / Dokter yang mengonfirmasi asesmen
                    ],
                    'item' => [
                        [
                            'linkId' => '1',
                            'text' => 'Apakah pasien mengalami batuk lebih dari 2 minggu?',
                            'answer' => [
                                [
                                    'valueBoolean' => true // Contoh jawaban berupa boolean
                                ]
                            ]
                        ],
                        [
                            'linkId' => '2',
                            'text' => 'Berapa berat badan pasien saat ini? (kg)',
                            'answer' => [
                                [
                                    'valueDecimal' => 65.5 // Contoh jawaban berupa angka desimal
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. QUESTIONNAIRE RESPONSE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_questionnaire_response',
                'label' => 'Update QuestionnaireResponse',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/QuestionnaireResponse/{id}',
                'description' => 'Memperbarui data jawaban kuesioner secara keseluruhan berdasarkan ID QuestionnaireResponse. ID di dalam body harus disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'QuestionnaireResponse ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'QuestionnaireResponse',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'questionnaire' => 'https://fhir.kemkes.go.id/Questionnaire/Q0001',
                    'status' => 'completed',
                    'subject' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29'
                    ],
                    'authored' => '2026-05-29T17:15:00+07:00', // Waktu modifikasi data jawaban
                    'author' => [
                        'reference' => 'Practitioner/N10000001'
                    ],
                    'item' => [
                        [
                            'linkId' => '1',
                            'text' => 'Apakah pasien mengalami batuk lebih dari 2 minggu?',
                            'answer' => [
                                [
                                    'valueBoolean' => false // Contoh pembaruan/koreksi jawaban dari true ke false
                                ]
                            ]
                        ],
                        [
                            'linkId' => '2',
                            'text' => 'Berapa berat badan pasien saat ini? (kg)',
                            'answer' => [
                                [
                                    'valueDecimal' => 65.5
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ]
    ];