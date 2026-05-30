<?php

    // =====================================================
    // ENCOUNTER
    // =====================================================

    return [
        'label' => 'Encounter',
        'icon' => '📅',
        'description' => 'Patient encounters',
        'endpoints' => [

            // ==========================================================
            // 2. ENCOUNTER - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_encounter',
                'label' => 'Create Encounter',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Encounter',
                'description' => 'Mencatatkan data kunjungan atau interaksi klinis baru pasien saat memulai pelayanan di fasilitas kesehatan.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Encounter',
                    'status' => 'arrived', // planned | arrived | triaged | in-progress | onleave | finished | cancelled +
                    'class' => [
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                        'code' => 'AMB', // AMB (ambulatory/rawat jalan), IMP (inpatient/rawat inap), EMER (emergency)
                        'display' => 'ambulatory'
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}', // ID Pasien riil dari SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'participant' => [
                        [
                            'type' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                            'code' => 'PPRF',
                                            'display' => 'primary performer'
                                        ]
                                    ]
                                ]
                            ],
                            'individual' => [
                                'reference' => 'Practitioner/{practitioner_id}', // ID NIK/SatuSehat Dokter DPJP
                                'display' => 'Nama Dokter Beserta Gelar'
                            ]
                        ]
                    ],
                    'period' => [
                        'start' => '2026-05-29T08:00:00+07:00' // Waktu pasien mulai melakukan registrasi/kedatangan
                    ],
                    'location' => [
                        [
                            'location' => [
                                'reference' => 'Location/{location_id}', // ID Location Ruangan/Poli di SATUSEHAT
                                'display' => 'Poliklinik Penyakit Dalam - Ruang 102'
                            ],
                            'status' => 'active'
                        ]
                    ],
                    'statusHistory' => [
                        [
                            'status' => 'arrived',
                            'period' => [
                                'start' => '2026-05-29T08:00:00+07:00'
                            ]
                        ]
                    ],
                    'serviceProvider' => [
                        'reference' => 'Organization/{org_id}' // ID Organisasi/Faskes (Kode RS/Klinik di SATUSEHAT)
                    ]
                ]
            ],

            // ==========================================================
            // 3. ENCOUNTER - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_encounter',
                'label' => 'Update Encounter',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Encounter/{id}',
                'description' => 'Memperbarui data interaksi kunjungan secara menyeluruh (misal: saat pelayanan selesai atau perubahan status riwayat kunjungan).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Encounter ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Encounter',
                    'id' => '{id}', // WAJIB ada di dalam body payload untuk metode PUT dan nilainya harus sama dengan URL
                    'status' => 'finished', // Skenario pembaruan: Status diubah menjadi finished (selesai pelayanan)
                    'class' => [
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                        'code' => 'AMB',
                        'display' => 'ambulatory'
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'participant' => [
                        [
                            'type' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                            'code' => 'PPRF',
                                            'display' => 'primary performer'
                                        ]
                                    ]
                                ]
                            ],
                            'individual' => [
                                'reference' => 'Practitioner/{practitioner_id}',
                                'display' => 'Nama Dokter Beserta Gelar'
                            ]
                        ]
                    ],
                    'period' => [
                        'start' => '2026-05-29T08:00:00+07:00',
                        'end' => '2026-05-29T09:30:00+07:00' // Ditambahkan waktu selesai pelayanan
                    ],
                    'location' => [
                        [
                            'location' => [
                                'reference' => 'Location/{location_id}',
                                'display' => 'Poliklinik Penyakit Dalam - Ruang 102'
                            ],
                            'status' => 'completed'
                        ]
                    ],
                    'statusHistory' => [
                        [
                            'status' => 'arrived',
                            'period' => [
                                'start' => '2026-05-29T08:00:00+07:00',
                                'end' => '2026-05-29T08:15:00+07:00'
                            ]
                        ],
                        [
                            'status' => 'in-progress',
                            'period' => [
                                'start' => '2026-05-29T08:15:00+07:00',
                                'end' => '2026-05-29T09:30:00+07:00'
                            ]
                        ],
                        [
                            'status' => 'finished',
                            'period' => [
                                'start' => '2026-05-29T09:30:00+07:00',
                                'end' => '2026-05-29T09:30:00+07:00'
                            ]
                        ]
                    ],
                    'serviceProvider' => [
                        'reference' => 'Organization/{org_id}'
                    ]
                ]
            ],

            // [
            //     'key' => 'update_encounter_status',
            //     'label' => 'Update Encounter (PUT/PATCH)',
            //     'method' => 'PUT',
            //     'path' => '/fhir-r4/v1/Encounter/{id}',
            //     'description' => 'Mengubah status kunjungan menjadi in-progress atau finished.',
            //     'params' => [
            //         ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Encounter SATUSEHAT', 'default' => '']
            //     ],
            //     'body' => [
            //         'resourceType' => 'Encounter',
            //         'status' => 'finished',
            //         'class' => [
            //             'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
            //             'code' => 'AMB',
            //             'display' => 'ambulatory'
            //         ],
            //         'subject' => [
            //             'reference' => 'Patient/{patient_id}'
            //         ],
            //         'period' => [
            //             'start' => '2026-05-26T09:00:00+07:00',
            //             'end' => '2026-05-26T09:30:00+07:00'
            //         ],
            //         'serviceProvider' => [
            //             'reference' => 'Organization/{org_id}'
            //         ]
            //     ]
            // ],

            // ==========================================================
            // 4. ENCOUNTER - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_encounter',
                'label' => 'Patch Encounter',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Encounter/{id}',
                'description' => 'Memperbarui satu atau beberapa properti rekam kunjungan secara spesifik (parsial) menggunakan format standar JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Encounter ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',
                        'path' => '/status', // Skenario singkat: Mengubah status kunjungan secara parsial tanpa mengirim ulang seluruh objek
                        'value' => 'in-progress'
                    ]
                ],
            ],
            [
                'key' => 'get_encounter',
                'label' => 'Get Encounter by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Encounter/{id}',
                'description' => 'Mengambil detail data kunjungan pasien.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Encounter', 'default' => '']
                ]
            ],

            // ==========================================================
            // 1. ENCOUNTER - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_encounter',
                'label' => 'Search Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Encounter?subject={patient_id}',
                'description' => 'Mencari rekam data kunjungan/interaksi medis pasien berdasarkan ID Pasien (subject) di SATUSEHAT.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ]
                ],
            ],

        ]
    ];