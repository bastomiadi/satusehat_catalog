<?php    
    // ==========================================
    // MASTER SARANA 
    // ==========================================

    return [

        'label' => 'MasterSaranaIndex',
        'icon' => '✅',
        'description' => 'Master Sarana Index',
        'endpoints' => [

            [
                'key' => 'get_master_sarana_index',
                'label' => 'Get Master Sarana Index',
                'method' => 'GET',
                'path' => '/masterdata/v1/mastersaranaindex/mastersarana?limit={limit}&page={page}&jenis_sarana={jenis_sarana}',
                'description' => 'Mencari dan menampilkan daftar data sarana fasilitas pelayanan kesehatan (Fasyankes) seperti RS, Klinik, Puskesmas, atau Praktik Mandiri.',
                'params' => [
                    [
                        'name' => 'limit',
                        'type' => 'text',
                        'placeholder' => 'Jumlah baris data per halaman (Wajib, Contoh: 10)',
                        'default' => '10'
                    ],
                    [
                        'name' => 'page',
                        'type' => 'text',
                        'placeholder' => 'Nomor halaman yang diinginkan (Wajib, Contoh: 1)',
                        'default' => '1'
                    ],
                    [
                        'name' => 'jenis_sarana',
                        'type' => 'text',
                        'placeholder' => 'Kode Jenis Sarana (104: RS, 103: Klinik, 102: Puskesmas, 101: Mandiri)',
                        'default' => '104'
                    ],
                    [
                        'name' => 'nama',
                        'type' => 'text',
                        'placeholder' => 'Pencarian berdasarkan Nama Fasyankes (Opsional)',
                        'default' => ''
                    ],
                    [
                        'name' => 'kode_provinsi',
                        'type' => 'text',
                        'placeholder' => '2 Digit Kode Dagri Provinsi (Opsional, Contoh: 35)',
                        'default' => ''
                    ],
                    [
                        'name' => 'kode_kabkota',
                        'type' => 'text',
                        'placeholder' => '4 Digit Kode Dagri Kab/Kota (Opsional, Contoh: 3603)',
                        'default' => ''
                    ],
                    [
                        'name' => 'status_sarana',
                        'type' => 'text',
                        'placeholder' => 'Status verifikasi (draft / verified / valid / reverified)',
                        'default' => ''
                    ],
                    [
                        'name' => 'sumber_identifier',
                        'type' => 'text',
                        'placeholder' => 'Sumber data (cth: satset, sisdmk_sarana, yankes_klinik)',
                        'default' => ''
                    ],
                    [
                        'name' => 'identifier_kode_sarana',
                        'type' => 'text',
                        'placeholder' => 'Kode sarana pada sistem sumber (cth: R3508055)',
                        'default' => ''
                    ]
                ],
            ],

        ]

    ];