<?php
    // =====================================================
    // LOCATION
    // =====================================================

    return [
        'label' => 'Location',
        'icon' => '📍',
        'description' => 'Healthcare locations',
        'endpoints' => [

            [
                'key' => 'create_location',
                'label' => 'Create Location',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Location',
                'description' => 'Mendaftarkan ruangan, poliklinik, atau tempat tidur (bed) baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Location',
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'http://sys-ids.kemkes.go.id/location/{org_id}', // Ganti dengan ID Organization Fasyankes Anda
                            'value' => 'G-Poli-Umum' // Kode unik internal ruangan dari SIMRS Anda
                        ]
                    ],
                    'status' => 'active',
                    'name' => 'Ruang Poli Umum',
                    'description' => 'Ruang Pemeriksaan Poli Umum Gedung A Lantai 1',
                    'mode' => 'instance',
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/location-type',
                                    'code' => 'AMB', // Contoh kode untuk Ambulatory / Rawat Jalan (Poli)
                                    'display' => 'Ambulatory'
                                ]
                            ]
                        ]
                    ],
                    'physicalType' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/location-physical-type',
                                'code' => 'ro', // 'ro' berarti Room (Ruangan)
                                'display' => 'Room'
                            ]
                        ]
                    ],
                    'managingOrganization' => [
                        'reference' => 'Organization/{org_id}' // ID Organization (Sub-unit Poli atau Fasyankes Utama) yang mengelola lokasi ini
                    ],
                    'partOf' => [
                        'reference' => 'Location/{location_id}' // Ganti dengan UUID Location Induk (misal: ID Gedung Utama Rumah Sakit yang sudah terdaftar di SATUSEHAT)
                    ]
                ]
            ],

            // [
            //     'key' => 'create_location',
            //     'label' => 'Create Location',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Location',
            //     'description' => 'Mendaftarkan ruangan/poliklinik baru di fasyankes.',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Location',
            //         'identifier' => [
            //             [
            //                 'system' => 'http://sys-ids.kemkes.go.id/location/{org_id}',
            //                 'value' => 'G-Poli-Umum'
            //             ]
            //         ],
            //         'status' => 'active',
            //         'name' => 'Ruang Poli Umum',
            //         'description' => 'Ruang Pemeriksaan Poli Umum Gedung A Lantai 1',
            //         'mode' => 'instance',
            //         'physicalType' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://terminology.hl7.org/CodeSystem/location-physical-type',
            //                     'code' => 'ro',
            //                     'display' => 'Room'
            //                 ]
            //             ]
            //         ],
            //         'managingOrganization' => [
            //             'reference' => 'Organization/{org_id}'
            //         ]
            //     ]
            // ],

            [
                'key' => 'get_location',
                'label' => 'Get Location by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Location/{id}',
                'description' => 'Mengambil data spesifik ruangan berdasarkan ID Location.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Location SATUSEHAT', 'default' => '']
                ]
            ],

            [
                'key' => 'update_location',
                'label' => 'Update Location',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Location/{id}',
                'description' => 'Memperbarui data lokasi (ruangan/poli/bed) secara keseluruhan berdasarkan ID Location. Data yang dikirim di body harus utuh.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Location ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Location',
                    'id' => '{id}', // WAJIB ADA di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'active',
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'http://sys-ids.kemkes.go.id/location/{org_id}', // ID Fasyankes Utama Anda
                            'value' => 'G-Poli-Umum' // Kode unik internal dari SIMRS Anda
                        ]
                    ],
                    'name' => 'Ruang Poli Umum (Updated)', // Nama baru atau nama yang diperbaiki
                    'description' => 'Ruang Pemeriksaan Poli Umum Gedung A Lantai 1 - Fasilitas Tambahan',
                    'mode' => 'instance',
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/location-type',
                                    'code' => 'AMB',
                                    'display' => 'Ambulatory'
                                ]
                            ]
                        ]
                    ],
                    'physicalType' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/location-physical-type',
                                'code' => 'ro',
                                'display' => 'Room'
                            ]
                        ]
                    ],
                    'managingOrganization' => [
                        'reference' => 'Organization/{org_id}' // ID Organisasi pengelola
                    ],
                    'partOf' => [
                        'reference' => 'Location/3362d984-af65-43ac-8e5c-7db2b3be3f8b' // ID Gedung/Lokasi Induk
                    ]
                ]
            ],

            [
                'key' => 'patch_location',
                'label' => 'Patch Location',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Location/{id}',
                'description' => 'Memperbarui data lokasi (ruangan/poli/bed) secara parsial (sebagian) menggunakan format JSON Patch (misal: mengubah nama ruangan atau menonaktifkan ruangan).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Location ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/name',       // Target elemen yang ingin diubah (Contoh: Nama Ruangan)
                        'value' => 'Ruang Poli Umum Gedung A Lantai 1 (Updated)' // Nilai pengganti yang baru
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/status',     // Target elemen status operasional ruangan
                        'value' => 'inactive'    // Nilai pilihan: active / suspended / inactive
                    ]
                ]
            ],

            [
                'key' => 'search_location_by_identifier',
                'label' => 'Search Location by Identifier',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Location?identifier=http://sys-ids.kemkes.go.id/location/{parent_id}|{location_code}',
                'description' => 'Mencari data lokasi (ruangan/bed/poli) berdasarkan kombinasi ID Lokasi Induk dan Kode Identifikasi Lokasi Lokal.',
                'params' => [
                    [
                        'name' => 'parent_id',
                        'type' => 'text',
                        'placeholder' => 'ID Lokasi Induk (contoh: 1000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'location_code',
                        'type' => 'text',
                        'placeholder' => 'Nomor Identifikasi Lokasi (contoh: G-2-R-1A)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_location_by_name',
                'label' => 'Search Location by Name',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Location?name={name}',
                'description' => 'Mencari data lokasi berdasarkan nama ruangan/poli, baik sebagian atau lengkap.',
                'params' => [
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'placeholder' => 'Nama lokasi (contoh: ruang atau Poli Anak)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_location_by_organization',
                'label' => 'Search Location by Organization ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Location?organization={organization_id}',
                'description' => 'Mencari seluruh daftar lokasi yang berada di bawah naungan ID Organisasi (Fasyankes/Departemen) tertentu.',
                'params' => [
                    [
                        'name' => 'organization_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (contoh: 54278fdf-57f9-4e6f-aca4-be97ac12a3f7)',
                        'default' => ''
                    ]
                ],
            ],

        ]
    ];