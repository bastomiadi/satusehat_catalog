<?php    
    // =====================================================
    // RELATED PERSON
    // =====================================================

    return [
        'label' => 'RelatedPerson',
        'icon' => '👨‍👩‍👧',
        'description' => 'Related persons and caregivers',
        'endpoints' => [
            // [
            //     'key' => 'create_relatedperson',
            //     'label' => 'Create RelatedPerson',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/RelatedPerson',
            //     'description' => 'Create related person',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'RelatedPerson',
            //         'patient' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],

            // ==========================================================
            // 1. RELATED PERSON - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_related_person',
                'label' => 'Search Related Person',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/RelatedPerson?identifier=https://fhir.kemkes.go.id/id/nik|{nik}',
                'description' => 'Mencari data relasi/kerabat pasien berdasarkan NIK dari Related Person terkait.',
                'params' => [
                    [
                        'name' => 'nik',
                        'type' => 'text',
                        'placeholder' => '16 Digit NIK Kerabat/Wali Pasien (cth: 317301XXXXXXXXXX)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. RELATED PERSON - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_related_person',
                'label' => 'Create Related Person',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/RelatedPerson',
                'description' => 'Mendaftarkan data wali/kerabat/penanggung jawab pasien baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'RelatedPerson',
                    'meta' => [
                        'profile' => [
                            'https://fhir.kemkes.go.id/r4/StructureDefinition/RelatedPerson'
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001', // Ganti dengan ID Pasien Utama SATUSEHAT
                        'display' => 'Nama Pasien Utama'
                    ],
                    'relationship' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                                    'code' => 'MTH', // MTH = Mother (Ibu), FTH = Father (Ayah), SPS = Spouse (Pasangan)
                                    'display' => 'mother'
                                ]
                            ]
                        ]
                    ],
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik', // System pemetaan NIK Nasional
                            'value' => '317301XXXXXXXXXX' // Ganti dengan NIK Wali/Kerabat asli
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Nama Lengkap Wali Sesuai KTP'
                        ]
                    ],
                    'gender' => 'female', // Pilihan: male | female | other | unknown
                    'birthDate' => '1975-12-25', // Format: YYYY-MM-DD
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '0812XXXXXXXX',
                            'use' => 'mobile'
                        ]
                    ],
                    'address' => [
                        [
                            'use' => 'home',
                            'line' => [
                                'Alamat Lengkap Rumah Wali Sesuai KTP'
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. RELATED PERSON - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_related_person',
                'label' => 'Update Related Person',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/RelatedPerson/{id}',
                'description' => 'Memperbarui data wali/kerabat secara keseluruhan berdasarkan ID RelatedPerson. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'RelatedPerson ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'RelatedPerson',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'meta' => [
                        'profile' => [
                            'https://fhir.kemkes.go.id/r4/StructureDefinition/RelatedPerson'
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Utama'
                    ],
                    'relationship' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                                    'code' => 'MTH',
                                    'display' => 'mother'
                                ]
                            ]
                        ]
                    ],
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik',
                            'value' => '317301XXXXXXXXXX'
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Nama Lengkap Wali Setelah Diperbarui' // Contoh pembaruan nama
                        ]
                    ],
                    'gender' => 'female',
                    'birthDate' => '1975-12-25',
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '0813XXXXXXXX', // Contoh pembaruan nomor kontak telepon wali
                            'use' => 'mobile'
                        ]
                    ],
                    'address' => [
                        [
                            'use' => 'home',
                            'line' => [
                                'Alamat Lengkap Rumah Wali Sesuai KTP'
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. RELATED PERSON - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_related_person',
                'label' => 'Patch Related Person',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/RelatedPerson/{id}',
                'description' => 'Memperbarui data elemen kerabat pasien secara parsial (sebagian) menggunakan format array JSON Patch (misalnya merubah nomor telepon secara cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'RelatedPerson ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/telecom/0/value', // Target elemen nomor telepon dalam array struktur FHIR
                        'value' => '081599999999' // Nomor telepon pengganti baru
                    ]
                ],
            ],
        ]
    ];