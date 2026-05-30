<?php
return [

'label' => 'KYC',
'icon' => '✅',
'description' => 'KYC',
'endpoints' => [
    // ==========================================================
    // 1. KYC - GENERATE CHALLENGE CODE
    // ==========================================================
    [
        'key' => 'kyc_generate_challenge_code',
        'label' => 'KYC - Generate Challenge Code',
        'method' => 'POST',
        'path' => '/kyc/v1/challenge-code',
        'description' => 'Mendapatkan token challenge code untuk validasi identitas pasien berdasarkan NIK dan Nama.',
        'params' => [],
        'body' => [
            'metadata' => [
                'method' => 'request_per_nik'
            ],
            'data' => [
                'nik' => '317301XXXXXXXXXX', // 16 digit NIK Pasien yang akan divalidasi
                'name' => 'Budi Santoso'       // Nama Lengkap Pasien sesuai KTP
            ]
        ],
    ],

    // ==========================================================
    // 2. KYC - GENERATE VALIDATION URL (ENCRYPTED)
    // ==========================================================
    [
        'key' => 'kyc_generate_validation_url',
        'label' => 'KYC - Generate Validation URL',
        'method' => 'POST',
        'path' => '/kyc/v1/generate-url',
        'description' => 'Menghasilkan URL Validasi terenkripsi untuk diserahkan/ditampilkan kepada pasien (via web/QR).',
        'params' => [],
        'body' => [
            'agent_code' => 'kodetoko-atau-fasyankes', // Kode unik fasyankes pengirim permintaan
            'challenge_code' => 'xxxxx', // Isi dengan token challenge_code yang didapat dari API pertama di atas
            'public_key' => '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END PUBLIC KEY-----' // RSA Public Key milik SIMRS Anda
        ],
    ],
]
];