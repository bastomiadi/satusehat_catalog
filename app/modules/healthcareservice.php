<?php    
    // =====================================================
    // HEALTHCARE SERVICE
    // =====================================================

    return [
        'label' => 'HealthcareService',
        'icon' => '🏥',
        'description' => 'Healthcare services offered',
        'endpoints' => [
            [
                'key' => 'get_healthcareservice',
                'label' => 'Get HealthcareService',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/HealthcareService',
                'description' => 'Get healthcare services',
                'params' => [],
            ],
            // ==========================================================
            // 1. HEALTHCARE SERVICE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_healthcare_service',
                'label' => 'Search HealthcareService',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/HealthcareService?specialty={specialty_code}',
                'description' => 'Mencari rekam jenis kategori pelayanan (HealthcareService) yang tersedia di fasyankes berdasarkan Kode Spesialisasi/Keahlian klinis resmi.',
                'params' => [
                    [
                        'name' => 'specialty_code',
                        'type' => 'text',
                        'placeholder' => 'Kode Spesialisasi (cth: S001.09 untuk Anak)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. HEALTHCARE SERVICE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_healthcare_service',
                'label' => 'Create HealthcareService',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/HealthcareService',
                'description' => 'Mendaftarkan jenis ketegoran bentuk unit pelayanan kesehatan/poliklinik operasional baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'HealthcareService',
                    'active' => true, // Status operasional jenis layanan di rumah sakit/klinik
                    'providedBy' => [
                        'reference' => 'Organization/10000004' // ID Organisasi/Fasyankes induk Anda di SATUSEHAT
                    ],
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/service-category',
                                    'code' => '17', // 17 = General Practice (Rumpun Praktik Umum/Poliklinik)
                                    'display' => 'General Practice'
                                ]
                            ]
                        ]
                    ],
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/service-type',
                                    'code' => '124', // Contoh kode jenis tipe spesifik (cth: Pediatric / Anak)
                                    'display' => 'Pediatric'
                                ]
                            ]
                        ]
                    ],
                    'specialty' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/clinical-specialty',
                                    'code' => 'S001.09', // Referensi kode keahlian klinis standar Kemenkes
                                    'display' => 'Pediatrie / Anak'
                                ]
                            ]
                        ]
                    ],
                    'name' => 'Poliklinik Tumbuh Kembang Anak Utama', // Nama operasional pelayanan di SIMRS Anda
                    'comment' => 'Pelayanan penanganan tumbuh kembang anak dan imunisasi berjadwal.'
                ]
            ],

            // ==========================================================
            // 3. HEALTHCARE SERVICE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_healthcare_service',
                'label' => 'Update HealthcareService',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/HealthcareService/{id}',
                'description' => 'Memperbarui data struktur pelayanan poliklinik secara keseluruhan berdasarkan ID HealthcareService. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'HealthcareService ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'HealthcareService',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'active' => true,
                    'providedBy' => [
                        'reference' => 'Organization/10000004'
                    ],
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/service-category',
                                    'code' => '17',
                                    'display' => 'General Practice'
                                ]
                            ]
                        ]
                    ],
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/service-type',
                                    'code' => '124',
                                    'display' => 'Pediatric'
                                ]
                            ]
                        ]
                    ],
                    'specialty' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/clinical-specialty',
                                    'code' => 'S001.09',
                                    'display' => 'Pediatrie / Anak'
                                ]
                            ]
                        ]
                    ],
                    'name' => 'Poliklinik Tumbuh Kembang Anak - Gedung B Lantai 2', // Contoh skenario perubahan nama lokasi gabungan unit pelayanan
                    'comment' => 'Pelayanan tumbuh kembang anak terpadu (Updated Pemindahan Gedung)'
                ]
            ],

            // ==========================================================
            // 4. HEALTHCARE SERVICE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_healthcare_service',
                'label' => 'Patch HealthcareService',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/HealthcareService/{id}',
                'description' => 'Memperbarui elemen informasi jenis layanan kesehatan secara parsial (sebagian) menggunakan skema standar JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'HealthcareService ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/active',     // Target elemen flag keaktifan
                        'value' => false         // Merubah menjadi false (misal jika layanan poliklinik tersebut sedang dinonaktifkan/tutup permanen)
                    ]
                ],
            ],
        ]
    ];