<?php    
    // ==========================================
    // MASTER WILAYAH 
    // ==========================================

    return [

        'label' => 'MasterWilayah',
        'icon' => '✅',
        'description' => 'Master Wilayah',
        'endpoints' => [

            // ==========================================
            // MASTER WILAYAH - VERSI 1 (BERBASIS KODE)
            // ==========================================
            [
                'key' => 'get_v1_provinces',
                'label' => 'V1 Master Wilayah - Provinsi',
                'method' => 'GET',
                'path' => '/masterdata/v1/provinces?codes={codes}',
                'description' => 'Mendapatkan data provinsi berdasarkan kode Kemendagri (bisa multi menggunakan koma).',
                'params' => [
                    [
                        'name' => 'codes',
                        'type' => 'text',
                        'placeholder' => 'Kode Provinsi (Wajib, Contoh: 11, 12)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'get_v1_cities',
                'label' => 'V1 Master Wilayah - Kota/Kabupaten',
                'method' => 'GET',
                'path' => '/masterdata/v1/cities?province_codes={province_codes}',
                'description' => 'Mendapatkan data kabupaten/kota berdasarkan kode provinsi induk (bisa difilter spesifik menggunakan parameter codes).',
                'params' => [
                    [
                        'name' => 'province_codes',
                        'type' => 'text',
                        'placeholder' => 'Kode Provinsi Induk (Wajib, Contoh: 11, 12)',
                        'default' => ''
                    ],
                    [
                        'name' => 'codes',
                        'type' => 'text',
                        'placeholder' => 'Kode Kab/Kota Spesifik (Opsional, Contoh: 1103, 1210)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'get_v1_districts',
                'label' => 'V1 Master Wilayah - Kecamatan',
                'method' => 'GET',
                'path' => '/masterdata/v1/districts?city_codes={city_codes}',
                'description' => 'Mendapatkan data kecamatan berdasarkan kode kabupaten/kota induk.',
                'params' => [
                    [
                        'name' => 'city_codes',
                        'type' => 'text',
                        'placeholder' => 'Kode Kab/Kota Induk (Wajib, Contoh: 1103, 1104)',
                        'default' => ''
                    ],
                    [
                        'name' => 'codes',
                        'type' => 'text',
                        'placeholder' => 'Kode Kecamatan Spesifik (Opsional, Contoh: 110301, 110302)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'get_v1_sub_districts',
                'label' => 'V1 Master Wilayah - Kelurahan/Desa',
                'method' => 'GET',
                'path' => '/masterdata/v1/sub-districts?district_codes={district_codes}&codes={codes}',
                'description' => 'Mendapatkan data kelurahan/desa berdasarkan kode kecamatan induk.',
                'params' => [
                    [
                        'name' => 'district_codes',
                        'type' => 'text',
                        'placeholder' => 'Kode Kecamatan Induk (Wajib, Contoh: 110301)',
                        'default' => ''
                    ],
                    [
                        'name' => 'codes',
                        'type' => 'text',
                        'placeholder' => 'Kode Kelurahan/Desa Spesifik (Wajib, Contoh: 1103012002)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================
            // MASTER WILAYAH - VERSI 2 (BERBASIS PAGINATION / CURSOR)
            // ==========================================
            [
                'key' => 'get_v2_provinces',
                'label' => 'V2 Master Wilayah - Provinsi',
                'method' => 'GET',
                'path' => '/masterdata/v2/provinces?current_page={current_page}',
                'description' => 'Menampilkan daftar seluruh provinsi menggunakan pagination Versi 2.',
                'params' => [
                    [
                        'name' => 'current_page',
                        'type' => 'text',
                        'placeholder' => 'Nomor halaman (Wajib, Contoh: 1)',
                        'default' => '1'
                    ],
                    [
                        'name' => 'codes',
                        'type' => 'text',
                        'placeholder' => 'Filter Kode Provinsi Spesifik (Opsional, Contoh: 11)',
                        'default' => ''
                    ],
                    [
                        'name' => 'next',
                        'type' => 'text',
                        'placeholder' => 'Cursor Next Token dari response meta (Opsional)',
                        'default' => ''
                    ],
                    [
                        'name' => 'prev',
                        'type' => 'text',
                        'placeholder' => 'Cursor Previous Token dari response meta (Opsional)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'get_v2_cities',
                'label' => 'V2 Master Wilayah - Kota/Kabupaten',
                'method' => 'GET',
                'path' => '/masterdata/v2/cities?current_page={current_page}',
                'description' => 'Menampilkan daftar seluruh kabupaten/kota menggunakan pagination Versi 2.',
                'params' => [
                    [
                        'name' => 'current_page',
                        'type' => 'text',
                        'placeholder' => 'Nomor halaman (Wajib, Contoh: 1)',
                        'default' => '1'
                    ],
                    [
                        'name' => 'province_codes',
                        'type' => 'text',
                        'placeholder' => 'Filter Kode Provinsi Induk (Opsional, Contoh: 11)',
                        'default' => ''
                    ],
                    [
                        'name' => 'codes',
                        'type' => 'text',
                        'placeholder' => 'Filter Kode Kab/Kota Spesifik (Opsional, Contoh: 1103)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'get_v2_districts',
                'label' => 'V2 Master Wilayah - Kecamatan',
                'method' => 'GET',
                'path' => '/masterdata/v2/districts?current_page={current_page}',
                'description' => 'Menampilkan daftar seluruh kecamatan menggunakan pagination Versi 2.',
                'params' => [
                    [
                        'name' => 'current_page',
                        'type' => 'text',
                        'placeholder' => 'Nomor halaman (Wajib, Contoh: 1)',
                        'default' => '1'
                    ],
                    [
                        'name' => 'city_codes',
                        'type' => 'text',
                        'placeholder' => 'Filter Kode Kab/Kota Induk (Opsional, Contoh: 1103)',
                        'default' => ''
                    ],
                    [
                        'name' => 'codes',
                        'type' => 'text',
                        'placeholder' => 'Filter Kode Kecamatan Spesifik (Opsional, Contoh: 110301)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'get_v2_sub_districts',
                'label' => 'V2 Master Wilayah - Kelurahan/Desa',
                'method' => 'GET',
                'path' => '/masterdata/v2/sub-districts?current_page={current_page}',
                'description' => 'Menampilkan daftar seluruh kelurahan/desa menggunakan pagination Versi 2.',
                'params' => [
                    [
                        'name' => 'current_page',
                        'type' => 'text',
                        'placeholder' => 'Nomor halaman (Wajib, Contoh: 1)',
                        'default' => '1'
                    ],
                    [
                        'name' => 'district_codes',
                        'type' => 'text',
                        'placeholder' => 'Filter Kode Kecamatan Induk (Opsional)',
                        'default' => ''
                    ],
                    [
                        'name' => 'codes',
                        'type' => 'text',
                        'placeholder' => 'Filter Kode Kelurahan Spesifik (Opsional, Contoh: 1103012002)',
                        'default' => ''
                    ]
                ],
            ],
        ]
    ];