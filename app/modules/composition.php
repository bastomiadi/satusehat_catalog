<?php 
    // =====================================================
    // COMPOSITION
    // =====================================================

    return [
        'label' => 'Composition',
        'icon' => '📄',
        'description' => 'Clinical documents and summaries',
        'endpoints' => [
            // [
            //     'key' => 'create_composition',
            //     'label' => 'Create Composition',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Composition',
            //     'description' => 'Create composition',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Composition',
            //         'status' => 'final',
            //         'type' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://terminology.hl7.org/CodeSystem/composition-type',
            //                     'code' => 'summary',
            //                     'display' => 'Summary'
            //                 ]
            //             ]
            //         ],
            //         'subject' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],
            // [
            //     'key' => 'get_composition',
            //     'label' => 'Get Composition by ID',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Composition/{id}',
            //     'description' => 'Get composition by ID',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Composition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            // [
            //     'key' => 'search_composition',
            //     'label' => 'Search Composition by Patient',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Composition?subject={patient_id}',
            //     'description' => 'Search compositions for patient',
            //     'params' => [
            //         [
            //             'name' => 'patient_id',
            //             'type' => 'text',
            //             'placeholder' => 'Patient ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            // [
            //     'key' => 'search_composition_by_encounter',
            //     'label' => 'Search Composition by Encounter ID',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Composition?encounter={encounter_id}',
            //     'description' => 'Mencari dokumen ringkasan klinis (Composition / Resume Medis) yang terkait dengan satu kunjungan (Encounter) tertentu.',
            //     'params' => [
            //         [
            //             'name' => 'encounter_id',
            //             'type' => 'text',
            //             'placeholder' => 'Format: UUID (contoh: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            
            // [
            //     'key' => 'update_composition',
            //     'label' => 'Update Composition',
            //     'method' => 'PUT',
            //     'path' => '/fhir-r4/v1/Composition/{id}',
            //     'description' => 'Update composition',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Composition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            // [
            //     'key' => 'delete_composition',
            //     'label' => 'Delete Composition',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/Composition/{id}',
            //     'description' => 'Delete composition',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Composition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            // [
            //     'key' => 'history_composition',
            //     'label' => 'History Composition',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Composition/{id}/_history',
            //     'description' => 'Get composition history',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Composition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'search_composition_by_encounter',
                'label' => 'Search Composition by Encounter ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Composition?encounter={encounter_id}',
                'description' => 'Mencari dokumen ringkasan klinis (Composition / Resume Medis) yang terkait dengan satu kunjungan (Encounter) tertentu.',
                'params' => [
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (contoh: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. COMPOSITION - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_composition',
                'label' => 'Search Composition By Patiend and Encounter Id',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Composition?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari dokumen klinis (Composition) berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
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
            // 2. COMPOSITION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_composition',
                'label' => 'Create Composition',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Composition',
                'description' => 'Mendaftarkan dokumen resume medis/klinis baru (Composition) ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Composition',
                    'status' => 'final', // Pilihan status: preliminary | final | amended | entered-in-error
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '11488-4', // Contoh kode LOINC untuk Consultation Note / Discharge Summary
                                'display' => 'Consultation note'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}', // Ganti dengan ID Pasien asli
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f' // Mengikat ID Encounter kunjungan
                    ],
                    'date' => '2026-05-29T16:00:00+07:00', // Waktu pembuatan dokumen
                    'author' => [
                        [
                            'reference' => 'Practitioner/N10000001', // ID Dokter pembuat dokumen
                            'display' => 'Nama Dokter Penanggung Jawab'
                        ]
                    ],
                    'title' => 'Resume Medis Pasien',
                    'custodian' => [
                        'reference' => 'Organization/{org_id}' // ID Fasyankes / Rumah Sakit utama
                    ],
                    'section' => [
                        [
                            'title' => 'Adverse Reactions',
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://loinc.org',
                                        'code' => '48765-2',
                                        'display' => 'Allergies and adverse reactions Document'
                                    ]
                                ]
                            ],
                            'text' => [
                                'status' => 'generated',
                                'div' => '<div xmlns="http://www.w3.org/1999/xhtml">Tidak ada riwayat alergi obat.</div>'
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. COMPOSITION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_composition',
                'label' => 'Update Composition',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Composition/{id}',
                'description' => 'Memperbarui isi dokumen klinis secara keseluruhan berdasarkan ID Composition. ID di dalam body harus sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Composition ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Composition',
                    'id' => '{id}', // WAJIB ada dan bernilai sama dengan parameter {id} di path URL
                    'status' => 'final',
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '11488-4',
                                'display' => 'Consultation note'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP (Updated)'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f'
                    ],
                    'date' => '2026-05-29T16:15:00+07:00', // Jam modifikasi dokumen
                    'author' => [
                        [
                            'reference' => 'Practitioner/N10000001',
                            'display' => 'Nama Dokter Penanggung Jawab'
                        ]
                    ],
                    'title' => 'Resume Medis Pasien (Terubah)',
                    'custodian' => [
                        'reference' => 'Organization/{org_id}'
                    ],
                    'section' => [
                        [
                            'title' => 'Adverse Reactions',
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://loinc.org',
                                        'code' => '48765-2',
                                        'display' => 'Allergies and adverse reactions Document'
                                    ]
                                ]
                            ],
                            'text' => [
                                'status' => 'generated',
                                'div' => '<div xmlns="http://www.w3.org/1999/xhtml">Ada riwayat alergi ringan terhadap Amoxicillin.</div>'
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. COMPOSITION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_composition',
                'label' => 'Patch Composition',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Composition/{id}',
                'description' => 'Memperbarui elemen dokumen klinis secara parsial (sebagian) menggunakan format array JSON Patch (misal: merevisi judul atau mengubah status dokumen saja).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Composition ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/title',      // Target elemen judul dokumen
                        'value' => 'Resume Medis Pasien Akhir (Revisi Otomatis)' // Nilai baru
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/status',     // Mengubah status dokumen dari draft / preliminary menjadi final
                        'value' => 'final'
                    ]
                ],
            ],
            
        ]
    ];