<?php
    // =====================================================
    // PRACTITIONER
    // =====================================================

    return [
        'label' => 'Practitioner',
        'icon' => '👨‍⚕️',
        'description' => 'Healthcare practitioners',
        'endpoints' => [

            [
                'key' => 'search_practitioner_nik',
                'label' => 'Search Practitioner by NIK',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Practitioner?identifier=https://fhir.kemkes.go.id/id/nik|{nik}',
                'description' => 'Mencari ID SATUSEHAT Tenaga Kesehatan / Dokter berdasarkan NIK.',
                'params' => [
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'Masukkan NIK Dokter', 'default' => '']
                ]
            ],
            [
                'key' => 'get_practitioner_id',
                'label' => 'Get Practitioner by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Practitioner/{id}',
                'description' => 'Mengambil data profil dokter berdasarkan ID SATUSEHAT.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Practitioner SATUSEHAT', 'default' => '']
                ]
            ],

            [
                'key' => 'search_practitioner_by_gender_name_birthdate',
                'label' => 'Search Practitioner (Gender + Name + Birthdate)',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Practitioner?gender={gender}&name={name}&birthdate={birthdate}',
                'description' => 'Mencari data praktisi kesehatan menggunakan kombinasi Jenis Kelamin, Nama, dan Tanggal Lahir (Ketiga parameter ini WAJIB ada bersamaan).',
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
                        'placeholder' => 'Nama nakes (contoh: Voigt)',
                        'default' => ''
                    ],
                    [
                        'name' => 'birthdate',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD atau YYYY (contoh: 1945)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'history_practitioner',
                'label' => 'History Practitioner',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Practitioner/{id}/_history',
                'description' => 'Get practitioner history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Practitioner ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_practitioner',
            //     'label' => 'History Type Practitioner',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Practitioner/_history',
            //     'description' => 'Get all practitioner history',
            //     'params' => [],
            // ],
        ]
    ];