<?php    
    // =====================================================
    // PRACTITIONER ROLE
    // =====================================================

    return [
        'label' => 'PractitionerRole',
        'icon' => '👨‍⚕️',
        'description' => 'Practitioner roles and specialties',
        'endpoints' => [
            [
                'key' => 'get_practitionerrole',
                'label' => 'Get PractitionerRole',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/PractitionerRole',
                'description' => 'Get practitioner roles',
                'params' => [],
            ],
            // ==========================================================
            // 1. PRACTITIONER ROLE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_practitioner_role',
                'label' => 'Search PractitionerRole',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/PractitionerRole?practitioner={practitioner_id}',
                'description' => 'Mencari data peran praktisi (PractitionerRole) berdasarkan ID Practitioner (Tenaga Kesehatan) terkait.',
                'params' => [
                    [
                        'name' => 'practitioner_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Practitioner ID (cth: N10000001)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. PRACTITIONER ROLE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_practitioner_role',
                'label' => 'Create PractitionerRole',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/PractitionerRole',
                'description' => 'Mendaftarkan hubungan peran profesional Tenaga Kesehatan di fasyankes ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'PractitionerRole',
                    'active' => true, // Status keaktifan peran nakes di fasyankes
                    'practitioner' => [
                        'reference' => 'Practitioner/N10000001', // ID Praktisi/Dokter di SATUSEHAT
                        'display' => 'Nama Dokter Spesialis'
                    ],
                    'organization' => [
                        'reference' => 'Organization/10000004', // ID Organisasi/Fasyankes Anda
                        'display' => 'Nama Rumah Sakit / Klinik Lokal'
                    ],
                    'code' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/practitioner-role',
                                    'code' => 'doctor', // Peran utama nakes (cth: doctor, nurse, pharmacist)
                                    'display' => 'Doctor'
                                ]
                            ]
                        ]
                    ],
                    'specialty' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/clinical-specialty',
                                    'code' => 'S001.09', // Contoh kode spesialisasi (misal: Spesialis Anak / Penyakit Dalam)
                                    'display' => 'Pediatrie / Anak'
                                ]
                            ]
                        ]
                    ],
                    'location' => [
                        [
                            'reference' => 'Location/b016428c-4f1e-4503-a12b-3a3d582cdcf8', // ID Ruangan/Poli tempat bertugas
                            'display' => 'Poliklinik Anak Spesialis Lantai 2'
                        ]
                    ],
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '021-XXXXXXXX',
                            'use' => 'work'
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. PRACTITIONER ROLE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_practitioner_role',
                'label' => 'Update PractitionerRole',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/PractitionerRole/{id}',
                'description' => 'Memperbarui data peran praktisi secara keseluruhan berdasarkan ID PractitionerRole. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'PractitionerRole ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'PractitionerRole',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'active' => true,
                    'practitioner' => [
                        'reference' => 'Practitioner/N10000001',
                        'display' => 'Nama Dokter Spesialis'
                    ],
                    'organization' => [
                        'reference' => 'Organization/10000004',
                        'display' => 'Nama Rumah Sakit / Klinik Lokal'
                    ],
                    'code' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/practitioner-role',
                                    'code' => 'doctor',
                                    'display' => 'Doctor'
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
                                    'display' => 'Pediatrie / Anak (Updated)'
                                ]
                            ]
                        ]
                    ],
                    'location' => [
                        [
                            'reference' => 'Location/b016428c-4f1e-4503-a12b-3a3d582cdcf8',
                            'display' => 'Poliklinik Anak Utama (Pindahan Ruang Baru)' // Contoh skenario pembaruan lokasi poli bertugas
                        ]
                    ],
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '021-YYYYYYYY', // Contoh pembaruan nomor ekstensi ruangan poli
                            'use' => 'work'
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. PRACTITIONER ROLE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_practitioner_role',
                'label' => 'Patch PractitionerRole',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/PractitionerRole/{id}',
                'description' => 'Memperbarui elemen data peran praktisi secara parsial (sebagian) menggunakan format array JSON Patch (misalnya menonaktifkan status aktif nakes dengan cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'PractitionerRole ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/active',     // Target elemen status keaktifan peran
                        'value' => false         // Mengubah nilai menjadi false (contoh jika nakes sudah resign atau mutasi fasyankes)
                    ]
                ],
            ],
        ]
    ];