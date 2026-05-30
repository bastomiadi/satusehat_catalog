<?php 

return [

'label' => 'MasterKFA',
'icon' => '✅',
'description' => 'Master KFA',
'endpoints' => [
    // ==========================================================
    // 1. KFA V1 - HARGA PRODUK JKN
    // ==========================================================
    [
        'key' => 'get_kfa_v1_jkn_price',
        'label' => 'KFA V1 - Price JKN',
        'method' => 'GET',
        'path' => '/kfa/farmalkes-price-jkn?page={page}&limit={limit}&kfa_code={kfa_code}',
        'description' => 'Mendapatkan informasi data harga produk JKN berdasarkan kode unik produk KFA tertentu.',
        'params' => [
            [
                'name' => 'page',
                'type' => 'text',
                'placeholder' => 'Nomor halaman (Wajib, Contoh: 1)',
                'default' => '1'
            ],
            [
                'name' => 'limit',
                'type' => 'text',
                'placeholder' => 'Jumlah baris data per halaman (Wajib, Contoh: 50)',
                'default' => '50'
            ],
            [
                'name' => 'kfa_code',
                'type' => 'text',
                'placeholder' => 'Kode produk KFA yang dicari (Wajib, Contoh: 93004418)',
                'default' => ''
            ],
            [
                'name' => 'region_code',
                'type' => 'text',
                'placeholder' => 'Kode Regional JKN (Opsional, Contoh: regional1)',
                'default' => ''
            ],
            [
                'name' => 'document_ref',
                'type' => 'text',
                'placeholder' => 'Dokumen referensi / dasar hukum (Opsional)',
                'default' => ''
            ]
        ],
    ],

    // ==========================================================
    // 2. KFA V2 - DETAIL PRODUK (BY IDENTIFIER & CODE)
    // ==========================================================
    [
        'key' => 'get_kfa_v2_product_detail',
        'label' => 'KFA V2 - Product Detail',
        'method' => 'GET',
        'path' => '/kfa-v2/products?identifier={identifier}&code={code}',
        'description' => 'Mendapatkan data informasi detail produk farmasi/alkes berdasarkan tipe identifier (kfa, nie, atau lkpp).',
        'params' => [
            [
                'name' => 'identifier',
                'type' => 'text',
                'placeholder' => 'Pilihan: kfa (Kamus Obat) | nie (Izin BPOM) | lkpp (Harga E-Katalog)',
                'default' => 'kfa'
            ],
            [
                'name' => 'code',
                'type' => 'text',
                'placeholder' => 'Kode produk sesuai tipe identifier (Wajib, Contoh: 93004418)',
                'default' => ''
            ]
        ],
    ],

    // ==========================================================
    // 3. KFA V2 - PENCARIAN PRODUK DENGAN PAGINASI (BROWSE ALL)
    // ==========================================================
    [
        'key' => 'get_kfa_v2_products_all',
        'label' => 'KFA V2 - Search Products All',
        'method' => 'GET',
        'path' => '/kfa-v2/products/all?page={page}&size={size}&product_type={product_type}',
        'description' => 'Mencari sekumpulan daftar produk farmasi atau alat kesehatan berdasarkan kategori kata kunci atau rentang tanggal tertentu.',
        'params' => [
            [
                'name' => 'page',
                'type' => 'text',
                'placeholder' => 'Nomor halaman yang diinginkan (Wajib, Contoh: 1)',
                'default' => '1'
            ],
            [
                'name' => 'size',
                'type' => 'text',
                'placeholder' => 'Jumlah baris data per halaman (Wajib, Contoh: 100)',
                'default' => '100'
            ],
            [
                'name' => 'product_type',
                'type' => 'text',
                'placeholder' => 'Kategori produk (Wajib, Contoh: farmasi atau alkes)',
                'default' => 'farmasi'
            ],
            [
                'name' => 'keyword',
                'type' => 'text',
                'placeholder' => 'Kata kunci nama produk (Opsional, Contoh: glove, amoxicillin)',
                'default' => ''
            ],
            [
                'name' => 'farmalkes_type',
                'type' => 'text',
                'placeholder' => 'Kategori spesifik (Opsional, Contoh: vaccine)',
                'default' => ''
            ],
            [
                'name' => 'from_date',
                'type' => 'text',
                'placeholder' => 'Waktu mulai sinkronisasi Format: YYYY-MM-DD (Opsional)',
                'default' => ''
            ],
            [
                'name' => 'to_date',
                'type' => 'text',
                'placeholder' => 'Waktu akhir sinkronisasi Format: YYYY-MM-DD (Opsional)',
                'default' => ''
            ],
            [
                'name' => 'template_code',
                'type' => 'text',
                'placeholder' => 'Kode Produk Virtual/PAV KFA (Opsional)',
                'default' => ''
            ],
            [
                'name' => 'packaging_code',
                'type' => 'text',
                'placeholder' => 'Kode Kemasan/PAK KFA (Opsional)',
                'default' => ''
            ]
        ],
    ],
]
];