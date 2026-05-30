<?php
    // =====================================================
    // IMMUNIZATION
    // =====================================================

    return [
        'label' => 'Immunization',
        'icon' => '💉',
        'description' => 'Immunization records',
        'endpoints' => [

            // Endpoint Baru: Pencarian berdasarkan Patient ID
            [
                'key' => 'search_immunization_by_patient',
                'label' => 'Search Immunization by Patient ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Immunization?patient={patient_id}',
                'description' => 'Mencari dan menampilkan seluruh riwayat imunisasi/vaksinasi spesifik milik satu pasien.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Masukkan ID Patient SATUSEHAT',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'create_immunization',
            //     'label' => 'Create Immunization',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Immunization',
            //     'description' => 'Mencatat pemberian vaksin/imunisasi baru pada pasien sesuai standarisasi profil SATUSEHAT Kemenkes.',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Immunization',
            //         'status' => 'completed',
            //         'vaccineCode' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://sys-ids.kemkes.go.id/kfa',
            //                     'code' => '93001019',
            //                     'display' => 'Vaksin Hepatitis B Rekombinan 0.5 mL'
            //                 ]
            //             ]
            //         ],
            //         'patient' => [
            //             'reference' => 'Patient/{patient_id}',
            //             'display' => 'Nama Pasien'
            //         ],
            //         'encounter' => [
            //             'reference' => 'Encounter/{encounter_id}',
            //             'display' => 'Kunjungan Pemeriksaan'
            //         ],
            //         'occurrenceDateTime' => date('c'),
            //         'primarySource' => true,
            //         'performer' => [
            //             [
            //                 'actor' => [
            //                     'reference' => 'Practitioner/{practitioner_id}',
            //                     'display' => 'Nama Tenaga Kesehatan'
            //                 ]
            //             ]
            //         ],
            //         'reasonCode' => [
            //             [
            //                 'coding' => [
            //                     [
            //                         'system' => 'http://hl7.org/fhir/sid/icd-10',
            //                         'code' => 'Z24.4',
            //                         'display' => 'Need for immunization against viral hepatitis'
            //                     ]
            //                 ]
            //             ]
            //         ],
            //         'protocolApplied' => [
            //             [
            //                 'doseNumberPositiveInt' => 1
            //             ]
            //         ]
            //     ]
            // ],
            [
                'key' => 'get_immunization',
                'label' => 'Get Immunization by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Immunization/{id}',
                'description' => 'Mengambil detail data riwayat imunisasi berdasarkan ID.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'Immunization ID', 'default' => '']
                ]
            ],

            // ==========================================================
            // 1. IMMUNIZATION - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_immunization',
                'label' => 'Search Immunization',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Immunization?patient={patient_id}&date={date}',
                'description' => 'Mencari rekam medis tindakan pemberian vaksin (Immunization) berdasarkan ID Pasien (patient) dan/atau Tanggal Imunisasi (date).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'date',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD (Opsional, cth: 2022-01-11)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. IMMUNIZATION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_immunization',
                'label' => 'Create Immunization',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Immunization',
                'description' => 'Mendaftarkan data riwayat tindakan imunisasi/vaksinasi baru pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Immunization',
                    'status' => 'completed', // Pilihan status: completed | entered-in-error | not-done
                    'vaccineCode' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => 'vg0002', // Contoh Kode KFA rumpun vaksin (misal: Vaksin BCG / Hepatitis B)
                                'display' => 'Vaksin Campak Kering'
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
                    'occurrenceDateTime' => '2026-05-29T10:00:00+07:00', // Waktu penyuntikan vaksin dilakukan
                    'primarySource' => true, // Set true jika fasyankes Anda yang langsung menyuntikkan
                    'lotNumber' => 'BATCH-2026-XYZ', // Nomor Batch / Lot dari kemasan fisik vaksin
                    'route' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RouteOfAdministration',
                                'code' => 'IM', // IM = Intramuscular, ID = Intradermal, PO = Per Os (Oral)
                                'display' => 'Injection, intramuscular'
                            ]
                        ]
                    ],
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/{practitioner_id}', // ID Dokter/Perawat/Bidan pelaksana vaksinasi
                                'display' => 'Nama Tenaga Kesehatan Vaksinator'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/immunization-reason',
                                    'code' => 'BIAN', // Contoh alasan program pemerintah (cth: BIAN, BIAS, Rutin)
                                    'display' => 'Bulan Imunisasi Anak Nasional'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. IMMUNIZATION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_immunization',
                'label' => 'Update Immunization',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Immunization/{id}',
                'description' => 'Memperbarui data rekam medis imunisasi secara keseluruhan berdasarkan ID Immunization. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Immunization ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Immunization',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'completed',
                    'vaccineCode' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => 'vg0002',
                                'display' => 'Vaksin Campak Kering (Updated)'
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
                    'occurrenceDateTime' => '2026-05-29T10:00:00+07:00',
                    'primarySource' => true,
                    'lotNumber' => 'BATCH-2026-XYZ-KOREKSI', // Contoh pembaruan/koreksi nomor batch vaksin
                    'route' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RouteOfAdministration',
                                'code' => 'IM',
                                'display' => 'Injection, intramuscular'
                            ]
                        ]
                    ],
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/{practitioner_id}',
                                'display' => 'Nama Tenaga Kesehatan Vaksinator'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/immunization-reason',
                                    'code' => 'BIAN',
                                    'display' => 'Bulan Imunisasi Anak Nasional'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. IMMUNIZATION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_immunization',
                'label' => 'Patch Immunization',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Immunization/{id}',
                'description' => 'Memperbarui elemen tindakan imunisasi secara parsial (sebagian) menggunakan format array JSON Patch (misalnya merubah status data menjadi entered-in-error jika terjadi kesalahan input).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Immunization ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status tindakan
                        'value' => 'entered-in-error' // Mengubah status menjadi salah input / batal secara cepat
                    ]
                ],
            ],

        ]
    ];