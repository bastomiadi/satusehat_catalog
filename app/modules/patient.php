<?php

    // =====================================================
    // PATIENT
    // =====================================================

    return [
        'label' => 'Patient',
        'icon' => '👤',
        'description' => 'Patient management resources',
        'endpoints' => [

            // 1. USE CASE: PEMBUATAN PASIEN UMUM (BERDASARKAN NIK PASIEN)
            [
                'key' => 'create_patient_by_nik',
                'label' => 'Create Patient (by NIK)',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Patient',
                'description' => 'Mendaftarkan data pasien baru ke dalam ekosistem SATUSEHAT menggunakan nomor identitas NIK Pasien resmi.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Patient',
                    'active' => true,
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik', // System URL khusus NIK Pasien
                            'value' => '317301XXXXXXXXXX' // Ganti dengan 16 digit NIK Pasien asli
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Nama Lengkap Pasien Sesuai KTP'
                        ]
                    ],
                    'gender' => 'male', // Pilihan: male / female / other / unknown
                    'birthDate' => '1995-08-17', // Format: YYYY-MM-DD
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '0812XXXXXXXX',
                            'use' => 'mobile'
                        ]
                    ]
                ]
            ],

            // 2. USE CASE: PEMBUATAN PASIEN BAYI / NEONATUS (BERDASARKAN NIK IBU)
            [
                'key' => 'create_patient_by_nik_ibu',
                'label' => 'Create Patient (Neonates by NIK Ibu)',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Patient',
                'description' => 'Digunakan khusus pendaftaran Bayi Baru Lahir (Neonatus) yang belum memiliki NIK sendiri, diikat melalui NIK Ibu Kandung.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Patient',
                    'active' => true,
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik-ibu', // System URL khusus untuk melacak NIK Ibu Kandung bayi
                            'value' => '317301XXXXXXXXXX' // Ganti dengan 16 digit NIK Ibu Kandung
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Bayi Ny. Nama Ibu' // Standar penamaan sementara SIMRS/Pusdatin
                        ]
                    ],
                    'gender' => 'female',
                    'birthDate' => date('Y-m-d'), // Otomatis tanggal hari ini saat di-hit
                    'multipleBirthBoolean' => false, // Set true jika lahir kembar
                    'extension' => [
                        [
                            'url' => 'https://fhir.kemkes.go.id/StructureDefinition/patient-birthPlace',
                            'valueAddress' => [
                                'city' => 'Jepara',
                                'country' => 'ID'
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'patch_patient',
                'label' => 'Patch Patient',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Patient/{id}',
                'description' => 'Memperbarui data pasien secara parsial (sebagian) menggunakan format JSON Patch (misal: membetulkan penulisan nama pasien atau menonaktifkan status aktif pasien).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID (Format: ID Pasien SATUSEHAT)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/name/0/text', // Target elemen nama lengkap pasien dalam struktur array FHIR
                        'value' => 'Nama Pasien Setelah Diperbaiki' // Nilai koreksi nama yang baru
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/active',     // Target elemen status keaktifan data pasien
                        'value' => true          // Nilai boolean: true / false
                    ]
                ]
            ],

            [
                'key' => 'get_patient',
                'label' => 'Get Patient by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient/{id}',
                'description' => 'Get patient by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_patient_nik',
                'label' => 'Search Patient by NIK',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/nik|{nik}',
                'description' => 'Search patient by NIK',
                'params' => [
                    [
                        'name' => 'nik',
                        'type' => 'text',
                        'placeholder' => 'NIK',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_patient_by_nik_name_birthdate',
                'label' => 'Search Patient (Kombinasi 1: NIK + Nama + Tgl Lahir)',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/nik|{nik}&name={name}&birthdate={birthdate}',
                'description' => 'Mencari data pasien menggunakan kombinasi NIK, Nama, dan Tanggal Lahir (Ketiga parameter ini WAJIB ada bersamaan).',
                'params' => [
                    [
                        'name' => 'nik',
                        'type' => 'text',
                        'placeholder' => 'Masukkan 16 digit NIK pasien',
                        'default' => ''
                    ],
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'placeholder' => 'Nama pasien (contoh: smith)',
                        'default' => ''
                    ],
                    [
                        'name' => 'birthdate',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD (contoh: 1985-10-25)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_patient_by_gender_name_birthdate',
                'label' => 'Search Patient (Kombinasi 2: Gender + Nama + Tgl Lahir)',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient?gender={gender}&name={name}&birthdate={birthdate}',
                'description' => 'Mencari data pasien menggunakan kombinasi Jenis Kelamin, Nama, dan Tanggal Lahir (Ketiga parameter ini WAJIB ada bersamaan).',
                'params' => [
                    [
                        'name' => 'gender',
                        'type' => 'text',
                        'placeholder' => 'male atau female',
                        'default' => ''
                    ],
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'placeholder' => 'Nama pasien (contoh: budi)',
                        'default' => ''
                    ],
                    [
                        'name' => 'birthdate',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD (contoh: 1990-05-12)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_patient_by_nik_ibu',
                'label' => 'Search Patient (Kombinasi 3: Hanya NIK Ibu)',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/nik-ibu|{nik_ibu}',
                'description' => 'Mencari data pasien (biasanya bayi baru lahir / neonatus) berdasarkan NIK Ibu Kandung yang terdaftar.',
                'params' => [
                    [
                        'name' => 'nik_ibu',
                        'type' => 'text',
                        'placeholder' => 'Masukkan 16 digit NIK Ibu Kandung',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'search_patient_noka',
            //     'label' => 'Search Patient by Noka',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/noka|{noka}',
            //     'description' => 'Search patient by BPJS card number',
            //     'params' => [
            //         [
            //             'name' => 'noka',
            //             'type' => 'text',
            //             'placeholder' => 'Nomor Kartu BPJS',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'update_patient',
                'label' => 'Update Patient (Neonates/WNA Only)',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Patient/{id}',
                'description' => 'Memperbarui data pasien (misal: Bayi yang baru mendapatkan nama tetap atau NIK). Harus mengirimkan data lengkap, bukan parsial.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (e.g., P00012345)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Patient',
                    'id' => '{id}', // ID ini harus sama dengan ID yang ada di URL path
                    'active' => true,
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik',
                            'value' => '3315xxxxxxxxxxxx' // Contoh jika NIK bayi sudah terbit
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Ahmad Fauzi' // Mengubah nama sementara (Bayi Ny. S) menjadi nama asli
                        ]
                    ],
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '08123456789',
                            'use' => 'mobile'
                        ]
                    ],
                    'gender' => 'male',
                    'birthDate' => '2026-05-01',
                    'multipleBirthBoolean' => false,
                    'address' => [
                        [
                            'use' => 'home',
                            'line' => ['Jl. Raya No. 10'],
                            'city' => 'Jepara',
                            'postalCode' => '59411',
                            'country' => 'ID',
                            'extension' => [
                                [
                                    'url' => 'https://fhir.kemkes.go.id/StructureDefinition/administrativeCode',
                                    'valueExtension' => [
                                        ['url' => 'province', 'valueCode' => '33'],
                                        ['url' => 'city', 'valueCode' => '3320'], // Contoh kode wilayah Jepara
                                        ['url' => 'district', 'valueCode' => '3320xx'],
                                        ['url' => 'village', 'valueCode' => '3320xxxxxx']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'extension' => [
                        [
                            'url' => 'https://fhir.kemkes.go.id/StructureDefinition/patient-birthPlace',
                            'valueAddress' => [
                                'city' => 'Jepara',
                                'country' => 'ID'
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'patient_history',
                'label' => 'Patient History',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient/{id}/_history',
                'description' => 'Get patient history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

        ]
    ];