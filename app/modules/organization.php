<?php
    // =====================================================
    // ORGANIZATION
    // =====================================================

    return [
        'label' => 'Organization',
        'icon' => '🏢',
        'description' => 'Healthcare organizations',
        'endpoints' => [

            [
                'key' => 'get_organization',
                'label' => 'Get Organization',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Organization/{id}',
                'description' => 'Get organization',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Organization ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_organization',
                'label' => 'Search Organization',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Organization?name={name}',
                'description' => 'Search organizations by name',
                'params' => [
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'placeholder' => 'Organization Name',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_organization_by_partof',
                'label' => 'Search Organization by Parent ID (Part Of)',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Organization?partof={parent_organization_id}',
                'description' => 'Mencari data sub-organisasi (seperti tingkatan unit/instalasi/departemen) yang bernaung di bawah ID Organisasi Induk tertentu.',
                'params' => [
                    [
                        'name' => 'parent_organization_id',
                        'type' => 'text',
                        'placeholder' => 'ID Organisasi Induk (contoh: 10000004)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'create_organization',
                'label' => 'Create Organization (Sub-Unit/Poli)',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Organization',
                'description' => 'Mendaftarkan data sub-organisasi baru (seperti tingkat instalasi, departemen, atau poli) di bawah naungan fasyankes utama.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Organization',
                    'active' => true,
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'http://sys-ids.kemkes.go.id/organization/100028344', // Ganti dengan ID Organisasi Utama/Fasyankes Anda
                            'value' => 'POLI-ANAK' // Kode unik internal dari SIMRS Anda untuk unit ini
                        ]
                    ],
                    'name' => 'Poli Anak dan Tumbuh Kembang', // Contoh nama organisasi/unit pelayanan yang jelas
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                                    'code' => 'dept', // Menggunakan 'dept' (Hospital Department) untuk unit/poli internal
                                    'display' => 'Hospital Department'
                                ]
                            ]
                        ]
                    ],
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '021-xxxxxx',
                            'use' => 'work'
                        ]
                    ],
                    'partOf' => [
                        'reference' => 'Organization/100028344' // WAJIB: Isi dengan ID Organisasi Utama Fasyankes Anda di SATUSEHAT
                    ]
                ]
            ],

            // [
            //     'key' => 'create_organization',
            //     'label' => 'Create Organization',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Organization',
            //     'description' => 'Create organization',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Organization',
            //         'name' => '',
            //         'type' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
            //                     'code' => 'prov',
            //                     'display' => 'Healthcare Provider'
            //                 ]
            //             ]
            //         ]
            //     ]
            // ],

            // [
            //     'key' => 'update_organization',
            //     'label' => 'Update Organization',
            //     'method' => 'PUT',
            //     'path' => '/fhir-r4/v1/Organization/{id}',
            //     'description' => 'Update organization',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Organization ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'update_organization',
                'label' => 'Update Organization',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Organization/{id}',
                'description' => 'Memperbarui data organisasi/fasyankes/poli secara keseluruhan berdasarkan ID Organization. Data yang dikirim di body harus utuh.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Organization ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Organization',
                    'id' => '{id}', // WAJIB ADA di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'active' => true,
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'http://sys-ids.kemkes.go.id/organization/100028344', // Ganti dengan ID Fasyankes Utama Anda
                            'value' => 'POLI-ANAK' // Kode internal SIMRS Anda
                        ]
                    ],
                    'name' => 'Poli Anak dan Tumbuh Kembang (Updated)', // Nama baru atau nama yang diperbaiki
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                                    'code' => 'dept',
                                    'display' => 'Hospital Department'
                                ]
                            ]
                        ]
                    ],
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '021-5551234', // Contoh pembaruan nomor telepon unit
                            'use' => 'work'
                        ]
                    ],
                    'partOf' => [
                        'reference' => 'Organization/100028344' // ID Organisasi Utama/Rumah Sakit Anda
                    ]
                ]
            ],

            [
                'key' => 'patch_organization',
                'label' => 'Patch Organization',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Organization/{id}',
                'description' => 'Memperbarui data organisasi/poli secara parsial (sebagian) menggunakan format JSON Patch (misal: mengubah nama atau menonaktifkan unit).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Organization ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/name',       // Target elemen yang ingin diubah (Contoh: Mengubah Nama Poli)
                        'value' => 'Poli Anak dan Tumbuh Kembang Utama' // Nilai penganti baru
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/active',     // Contoh lain jika ingin menonaktifkan unit fasyankes
                        'value' => true          // Nilai boolean: true / false
                    ]
                ]
            ],

            [
                'key' => 'history_organization',
                'label' => 'History Organization',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Organization/{id}/_history',
                'description' => 'Get organization history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Organization ID',
                        'default' => ''
                    ]
                ],
            ],
        ]
    ];