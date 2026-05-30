<?php
    // =====================================================
    // EPISODE OF CARE
    // =====================================================

    return [
        'label' => 'EpisodeOfCare',
        'icon' => '🔄',
        'description' => 'Episode of care',
        'endpoints' => [

            // ==========================================================
            // 2. EPISODE OF CARE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_episode_of_care',
                'label' => 'Create EpisodeOfCare',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/EpisodeOfCare',
                'description' => 'Mendaftarkan data asuhan perawatan berkelanjutan baru untuk program kesehatan spesifik pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'EpisodeOfCare',
                    'status' => 'active', // Status program: planned | waitlist | active | onhold | finished | cancelled | entered-in-error
                    'statusHistory' => [
                        [
                            'status' => 'active',
                            'period' => [
                                'start' => '2026-05-29T08:00:00+07:00' // Riwayat pencatatan waktu status aktif dimulai
                            ]
                        ]
                    ],
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/episodeofcare-type',
                                    'code' => 'TB', // Contoh kode jenis manajemen program kesehatan (cth: Tuberkulosis)
                                    'display' => 'Tuberkulosis'
                                ]
                            ]
                        ]
                    ],
                    'diagnosis' => [
                        [
                            'condition' => [
                                'reference' => 'Condition/{condition_id}', // ID Diagnosis dasar dari resource Condition
                                'display' => 'Tuberculosis of lung, bacteriologically and histologically confirmed'
                            ],
                            'role' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
                                        'code' => 'CC', // CC = Chief Complaint / Diagnosis Utama Rangkaian Asuhan
                                        'display' => 'Chief complaint'
                                    ]
                                ]
                            ],
                            'rank' => 1
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/{patient_id}', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'managingOrganization' => [
                        'reference' => 'Organization/{org_id}' // ID Organisasi/Fasyankes penanggung jawab program asuhan
                    ],
                    'period' => [
                        'start' => '2026-05-29T08:00:00+07:00' // Rentang waktu asuhan keperawatan dimulai
                    ],
                    'careManager' => [
                        'reference' => 'Practitioner/{practitioner_id}', // Dokter penanggung jawab kasus (DPJP) utama
                        'display' => 'Nama Dokter DPJP Utama'
                    ]
                ]
            ],

            [
                'key' => 'get_episode_of_care',
                'label' => 'Get EpisodeOfCare by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/EpisodeOfCare/{id}',
                'description' => 'Get episode of care by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. EPISODE OF CARE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_episode_of_care',
                'label' => 'Search EpisodeOfCare',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/EpisodeOfCare?subject={patient_id}',
                'description' => 'Mencari data rekam rangkaian asuhan perawatan berkelanjutan (EpisodeOfCare) berdasarkan ID Pasien (subject).',
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
            // 3. EPISODE OF CARE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_episode_of_care',
                'label' => 'Update EpisodeOfCare',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/EpisodeOfCare/{id}',
                'description' => 'Memperbarui rekam data program asuhan berkelanjutan secara keseluruhan berdasarkan ID EpisodeOfCare. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'EpisodeOfCare',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'finished', // Skenario pembaruan: status diubah menjadi finished karena rangkaian terapi/asuhan telah selesai
                    'statusHistory' => [
                        [
                            'status' => 'active',
                            'period' => [
                                'start' => '2026-05-29T08:00:00+07:00',
                                'end' => '2026-05-29T10:00:00+07:00'
                            ]
                        ],
                        [
                            'status' => 'finished',
                            'period' => [
                                'start' => '2026-05-29T10:00:00+07:00'
                            ]
                        ]
                    ],
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/episodeofcare-type',
                                    'code' => 'TB',
                                    'display' => 'Tuberkulosis'
                                ]
                            ]
                        ]
                    ],
                    'diagnosis' => [
                        [
                            'condition' => [
                                'reference' => 'Condition/{condition_id}',
                                'display' => 'Tuberculosis of lung, bacteriologically and histologically confirmed'
                            ],
                            'role' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
                                        'code' => 'CC',
                                        'display' => 'Chief complaint'
                                    ]
                                ]
                            ],
                            'rank' => 1
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'managingOrganization' => [
                        'reference' => 'Organization/{org_id}',
                    ],
                    'period' => [
                        'start' => '2026-05-29T08:00:00+07:00',
                        'end' => '2026-05-29T10:00:00+07:00' // Tanggal asuhan resmi dinyatakan berakhir/tutup kasus
                    ],
                    'careManager' => [
                        'reference' => 'Practitioner/{practitioner_id}',
                        'display' => 'Nama Dokter DPJP Utama'
                    ]
                ]
            ],

            // ==========================================================
            // 4. EPISODE OF CARE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_episode_of_care',
                'label' => 'Patch EpisodeOfCare',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/EpisodeOfCare/{id}',
                'description' => 'Memperbarui status atau elemen data episode asuhan pasien secara parsial (sebagian) menggunakan format array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',           // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',         // Target elemen status asuhan
                        'value' => 'onhold'          // Mengubah status menjadi ditangguhkan sementara (onhold) secara instan
                    ]
                ],
            ],

            // [
            //     'key' => 'delete_episode_of_care',
            //     'label' => 'Delete EpisodeOfCare',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/EpisodeOfCare/{id}',
            //     'description' => 'Delete episode of care',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'EpisodeOfCare ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_episode_of_care',
                'label' => 'History EpisodeOfCare',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/EpisodeOfCare/{id}/_history',
                'description' => 'Get episode of care history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_episode_of_care',
            //     'label' => 'History Type EpisodeOfCare',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/EpisodeOfCare/_history',
            //     'description' => 'Get all episode of care history',
            //     'params' => [],
            // ],
        ]
    ];