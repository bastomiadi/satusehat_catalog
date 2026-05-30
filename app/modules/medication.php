<?php 
    // =====================================================
    // MEDICATION
    // =====================================================

    return [
        'label' => 'Medication',
        'icon' => '💊',
        'description' => 'Medication resources including ingredients and formulations',
        'endpoints' => [
            [
                'key' => 'create_medication_us',
                'label' => 'Create Medication (US Formulary)',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Medication',
                'description' => 'Mendaftarkan sediaan obat berdasarkan kode US-Formulary FHIR.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Medication',
                    'meta' => [
                        'profile' => ['http://hl7.org/fhir/StructureDefinition/Medication']
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/us-formulary',
                                'code' => '4000001',
                                'display' => 'Acetaminophen 500mg tablet'
                            ]
                        ],
                        'text' => 'Acetaminophen 500mg oral tablet'
                    ],
                    'status' => 'active',
                    'form' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm',
                                'code' => 'TAB',
                                'display' => 'Tablet'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'key' => 'create_medication_kfa',
                'label' => 'Create Medication',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Medication',
                'description' => 'Mendaftarkan sediaan kamus obat fasyankes berdasarkan kode KFA.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Medication',
                    'meta' => [
                        'profile' => ['https://fhir.kemkes.go.id/StructureDefinition/Medication']
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => '93000123',
                                'display' => 'Paracetamol 500 mg Tablet'
                            ]
                        ]
                    ],
                    'status' => 'active',
                    'form' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => 'BS023',
                                'display' => 'Tablet'
                            ]
                        ]
                    ]
                ]
            ],
            
            // ==========================================================
            // 2. MEDICATION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_medication',
                'label' => 'Create Medication',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Medication',
                'description' => 'Mendaftarkan definisi item obat baru (berdasarkan kode KFA Kemenkes) ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Medication',
                    'meta' => [
                        'profile' => [
                            'https://fhir.kemkes.go.id/r4/StructureDefinition/Medication'
                        ]
                    ],
                    'identifier' => [
                        [
                            'system' => 'http://sys-ids.kemkes.go.id/medication/{org_id}', // Menggunakan Kode Fasyankes (Org ID) Anda
                            'value' => 'OBAT-AMOX-500' // Kode internal item obat dari SIMRS / Inventory Apotek Anda
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => '93000021', // Contoh Kode Produk KFA (Kamus Farmasi & Alkes) Kemenkes
                                'display' => 'Amoxicillin 500 mg Kaplet (contoh)'
                            ]
                        ]
                    ],
                    'status' => 'active', // Status obat: active | inactive | entered-in-error
                    'form' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm',
                                'code' => 'CAP', // CAP = Kapsul / Kaplet, TAB = Tablet, SUSP = Suspensi, dll.
                                'display' => 'Capsule'
                            ]
                        ]
                    ],
                    'extension' => [
                        [
                            'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
                            'valueCodeableConcept' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                                        'code' => 'NC', // NC = Non-Compound (Obat Jadi), CO = Compound (Obat Racikan)
                                        'display' => 'Non-Compound'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // ==========================================================
            // 1. MEDICATION - DETAIL DATA (GET)
            // ==========================================================
            [
                'key' => 'get_medication_detail',
                'label' => 'Get Medication Detail',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Medication/{id}',
                'description' => 'Mendapatkan data informasi detail spesifik obat yang terdaftar di SATUSEHAT berdasarkan ID Resource Medication.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Medication Resource ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'search_medication',
                'label' => 'Search Medication',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Medication?identifier={identifier}',
                'description' => 'Search medication by identifier',
                'params' => [
                    [
                        'name' => 'identifier',
                        'type' => 'text',
                        'placeholder' => 'Identifier',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'search_medication_status',
                'label' => 'Search Medication by Status',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Medication?status={status}',
                'description' => 'Search medication by status',
                'params' => [
                    [
                        'name' => 'status',
                        'type' => 'text',
                        'placeholder' => 'Status (active|inactive|entered-in-error)',
                        'default' => 'active'
                    ]
                ],
            ],
            
            // ==========================================================
            // 3. MEDICATION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_medication',
                'label' => 'Update Medication',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Medication/{id}',
                'description' => 'Memperbarui data katalog informasi obat secara keseluruhan berdasarkan ID Medication. ID di bodi wajib ada dan sama dengan URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Medication Resource ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Medication',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'meta' => [
                        'profile' => [
                            'https://fhir.kemkes.go.id/r4/StructureDefinition/Medication'
                        ]
                    ],
                    'identifier' => [
                        [
                            'system' => 'http://sys-ids.kemkes.go.id/medication/10000004',
                            'value' => 'OBAT-AMOX-500'
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => '93000021',
                                'display' => 'Amoxicillin 500 mg Kaplet (Data Terpelihara)'
                            ]
                        ]
                    ],
                    'status' => 'active',
                    'form' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm',
                                'code' => 'CAP',
                                'display' => 'Capsule'
                            ]
                        ]
                    ],
                    'extension' => [
                        [
                            'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
                            'valueCodeableConcept' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                                        'code' => 'NC',
                                        'display' => 'Non-Compound'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. MEDICATION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            // [
            //     'key' => 'patch_medication',
            //     'label' => 'Patch Medication',
            //     'method' => 'PATCH',
            //     'path' => '/Medication/{id}',
            //     'description' => 'Memperbarui status aktif/nonaktif item obat tertentu secara parsial (sebagian) menggunakan skema operasi array JSON Patch.',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Medication Resource ID (Format: UUID)',
            //             'default' => ''
            //         ]
            //     ],
            //     'body' => [
            //         [
            //             'op' => 'replace',       // Operasi penggantian nilai elemen target
            //             'path' => '/status',     // Elemen target status obat
            //             'value' => 'inactive'    // Mengubah status menjadi inactive (misal obat sudah ditarik/tidak digunakan lagi)
            //         ]
            //     ],
            // ],
            
            [
                'key' => 'patch_medication',
                'label' => 'Patch Medication Status',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Medication/{id}',
                'description' => 'Memperbarui data obat secara parsial (misal: menonaktifkan obat tanpa menghapus data kamus obat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Medication ID (e.g., 10000004)',
                        'default' => ''
                    ]
                ],
                'headers' => [
                    'Content-Type' => 'application/json-patch+json' // Wajib untuk JSON Patch
                ],
                'body' => [
                    [
                        'op' => 'replace',      // Operasi: bisa 'replace', 'add', atau 'remove'
                        'path' => '/status',     // Target field yang ingin diubah
                        'value' => 'inactive'    // Nilai baru yang diinginkan (misal: me-nonaktifkan obat)
                    ]
                ]
            ],
            [
                'key' => 'history_medication',
                'label' => 'History Medication',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Medication/{id}/_history',
                'description' => 'Get medication history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Medication ID',
                        'default' => ''
                    ]
                ],
            ],
            // [
            //     'key' => 'history_type_medication',
            //     'label' => 'History Type Medication',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Medication/_history',
            //     'description' => 'Get all medication history',
            //     'params' => [],
            // ],
        ]
    ];