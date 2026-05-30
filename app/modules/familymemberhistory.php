<?php    
    // =====================================================
    // FAMILY MEMBER HISTORY
    // =====================================================

    return [
        'label' => 'FamilyMemberHistory',
        'icon' => '👨‍👩‍👧',
        'description' => 'Family member history and genetic information',
        'endpoints' => [
            // [
            //     'key' => 'create_familymemberhistory',
            //     'label' => 'Create FamilyMemberHistory',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/FamilyMemberHistory',
            //     'description' => 'Create family member history',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'FamilyMemberHistory',
            //         'status' => 'completed',
            //         'patient' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],
            // ==========================================================
            // 1. FAMILY MEMBER HISTORY - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_family_member_history',
                'label' => 'Search FamilyMemberHistory',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/FamilyMemberHistory?patient={patient_id}&relationship={relationship_code}',
                'description' => 'Mencari data riwayat kesehatan keluarga berdasarkan ID Pasien (patient) dan jenis hubungan kekeluargaan (relationship). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'relationship_code',
                        'type' => 'text',
                        'placeholder' => 'Kode Relasi (cth: FTH untuk Ayah, MTH untuk Ibu)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. FAMILY MEMBER HISTORY - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_family_member_history',
                'label' => 'Create FamilyMemberHistory',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/FamilyMemberHistory',
                'description' => 'Mendaftarkan data riwayat penyakit anggota keluarga baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'FamilyMemberHistory',
                    'status' => 'completed', // Pilihan status: partial | completed | entered-in-error | health-unknown
                    'patient' => [
                        'reference' => 'Patient/100000000001', // Ganti dengan ID Pasien Utama SATUSEHAT
                        'display' => 'Nama Pasien Utama'
                    ],
                    'relationship' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                                'code' => 'FTH', // FTH = Father (Ayah), MTH = Mother (Ibu)
                                'display' => 'father'
                            ]
                        ]
                    ],
                    'condition' => [
                        [
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://hl7.org/fhir/sid/icd-10',
                                        'code' => 'E11.9', // Contoh kode ICD-10 untuk Diabetes Melitus Tipe 2 tanpa komplikasi
                                        'display' => 'Type 2 diabetes mellitus without complications'
                                    ]
                                ]
                            ],
                            'outcome' => [
                                'coding' => [
                                    [
                                        'system' => 'http://snomed.info/sct',
                                        'code' => '419099009',
                                        'display' => 'Died' // Status kondisi kerabat (bisa diisi 'Died' jika sudah meninggal)
                                    ]
                                ]
                            ],
                            'note' => [
                                [
                                    'text' => 'Riwayat menderita diabetes melitus tipe 2 sejak usia 45 tahun.'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. FAMILY MEMBER HISTORY - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_family_member_history',
                'label' => 'Update FamilyMemberHistory',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/FamilyMemberHistory/{id}',
                'description' => 'Memperbarui data riwayat kesehatan keluarga secara keseluruhan berdasarkan ID FamilyMemberHistory. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'FamilyMemberHistory ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'FamilyMemberHistory',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'completed',
                    'patient' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Utama'
                    ],
                    'relationship' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                                'code' => 'FTH',
                                'display' => 'father'
                            ]
                        ]
                    ],
                    'condition' => [
                        [
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://hl7.org/fhir/sid/icd-10',
                                        'code' => 'E11.9',
                                        'display' => 'Type 2 diabetes mellitus without complications (Updated)'
                                    ]
                                ]
                            ],
                            'outcome' => [
                                'coding' => [
                                    [
                                        'system' => 'http://snomed.info/sct',
                                        'code' => '419099009',
                                        'display' => 'Died'
                                    ]
                                ]
                            ],
                            'note' => [
                                [
                                    'text' => 'Riwayat menderita diabetes melitus tipe 2 sejak usia 45 tahun (Data telah dikonfirmasi ulang).'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. FAMILY MEMBER HISTORY - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_family_member_history',
                'label' => 'Patch FamilyMemberHistory',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/FamilyMemberHistory/{id}',
                'description' => 'Memperbarui data riwayat keluarga pasien secara parsial (sebagian) menggunakan format array JSON Patch (misalnya merubah status/kondisi secara cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'FamilyMemberHistory ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status
                        'value' => 'completed'   // Nilai pengganti status baru
                    ]
                ],
            ],
        ]
    ];