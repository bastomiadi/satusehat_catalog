<?php
/**
 * SATUSEHAT API Catalog - Complete Module Endpoints
 * Native PHP + Tailwind CSS
 * Version 2.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Load configuration
$config = include __DIR__ . '/../config/config.php';

// SATUSEHAT Configuration
$satusehat = $config['satusehat'];
$currentEnv = isset($_GET['env']) ? $_GET['env'] : 'sandbox';
$module = isset($_GET['module']) ? $_GET['module'] : 'patient';
$envConfig = $satusehat[$currentEnv];

$auth_url = $envConfig['auth_url'];
$base_url = $envConfig['base_url'];
$org_id = $envConfig['organization_id'];
$client_id = $envConfig['client_id'];
$client_secret = $envConfig['client_secret'];

// Token handling
$tokenCacheFile = __DIR__ . '/../storage/cache/token_' . $currentEnv . '.cache';
$tokenExpiryFile = __DIR__ . '/../storage/cache/token_' . $currentEnv . '.expiry';

// Get or refresh token
$token = '';
$tokenExpiry = 0;

if (file_exists($tokenCacheFile) && file_exists($tokenExpiryFile)) {
    $cachedToken = file_get_contents($tokenCacheFile);
    $cachedExpiry = (int)file_get_contents($tokenExpiryFile);
    
    // Only use cached token if it's still valid
    if ($cachedToken && time() < $cachedExpiry) {
        $token = $cachedToken;
        $tokenExpiry = $cachedExpiry;
    } else {
        // Clear expired cache
        @unlink($tokenCacheFile);
        @unlink($tokenExpiryFile);
    }
}

// If no valid token, get a new one
if (!$token) {
    $token = getAccessTokenCatalog($auth_url, $client_id, $client_secret, $tokenCacheFile, $tokenExpiryFile);
}

// Function to get access token
function getAccessTokenCatalog($auth_url, $client_id, $client_secret, $tokenCacheFile, $tokenExpiryFile) {
    // Check if we have a valid cached token
    if (file_exists($tokenCacheFile) && file_exists($tokenExpiryFile)) {
        $cachedToken = file_get_contents($tokenCacheFile);
        $cachedExpiry = (int)file_get_contents($tokenExpiryFile);
        
        if ($cachedToken && time() < $cachedExpiry) {
            return $cachedToken;
        }
        // Clear expired cache
        @unlink($tokenCacheFile);
        @unlink($tokenExpiryFile);
    }
    
    // Get new token using cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $auth_url . '/accesstoken?grant_type=client_credentials',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return null;
    }
    
    $result = json_decode($response, true);
    
    if (!$result || !isset($result['access_token'])) {
        return null;
    }
    
    // Cache the token
    $cacheDir = dirname($tokenCacheFile);
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    @file_put_contents($tokenCacheFile, $result['access_token']);
    @file_put_contents($tokenExpiryFile, time() + ($result['expires_in'] ?? 3600));
    
    return $result['access_token'];
};

$modules = [

    // =====================================================
    // PATIENT
    // =====================================================

    'patient' => [
        'label' => 'Patient',
        'icon' => '👤',
        'description' => 'Patient management resources',
        'endpoints' => [

            // 1. USE CASE: PEMBUATAN PASIEN UMUM (BERDASARKAN NIK PASIEN)
            [
                'key' => 'create_patient_by_nik',
                'label' => 'Create Patient (by NIK)',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Patient',
                'description' => 'Mendaftarkan data pasien baru ke dalam ekosistem SATUSEHAT menggunakan nomor identitas NIK Pasien resmi.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Patient',
                    'active' => true,
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik', // System URL khusus NIK Pasien
                            'value' => '317301XXXXXXXXXX' // Ganti dengan 16 digit NIK Pasien asli
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Nama Lengkap Pasien Sesuai KTP'
                        ]
                    ],
                    'gender' => 'male', // Pilihan: male / female / other / unknown
                    'birthDate' => '1995-08-17', // Format: YYYY-MM-DD
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '0812XXXXXXXX',
                            'use' => 'mobile'
                        ]
                    ]
                ]
            ],

            // 2. USE CASE: PEMBUATAN PASIEN BAYI / NEONATUS (BERDASARKAN NIK IBU)
            [
                'key' => 'create_patient_by_nik_ibu',
                'label' => 'Create Patient (Neonates by NIK Ibu)',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Patient',
                'description' => 'Digunakan khusus pendaftaran Bayi Baru Lahir (Neonatus) yang belum memiliki NIK sendiri, diikat melalui NIK Ibu Kandung.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Patient',
                    'active' => true,
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik-ibu', // System URL khusus untuk melacak NIK Ibu Kandung bayi
                            'value' => '317301XXXXXXXXXX' // Ganti dengan 16 digit NIK Ibu Kandung
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Bayi Ny. Nama Ibu' // Standar penamaan sementara SIMRS/Pusdatin
                        ]
                    ],
                    'gender' => 'female',
                    'birthDate' => date('Y-m-d'), // Otomatis tanggal hari ini saat di-hit
                    'multipleBirthBoolean' => false, // Set true jika lahir kembar
                    'extension' => [
                        [
                            'url' => 'https://fhir.kemkes.go.id/StructureDefinition/patient-birthPlace',
                            'valueAddress' => [
                                'city' => 'Jepara',
                                'country' => 'ID'
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'patch_patient',
                'label' => 'Patch Patient',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Patient/{id}',
                'description' => 'Memperbarui data pasien secara parsial (sebagian) menggunakan format JSON Patch (misal: membetulkan penulisan nama pasien atau menonaktifkan status aktif pasien).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID (Format: ID Pasien SATUSEHAT)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/name/0/text', // Target elemen nama lengkap pasien dalam struktur array FHIR
                        'value' => 'Nama Pasien Setelah Diperbaiki' // Nilai koreksi nama yang baru
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/active',     // Target elemen status keaktifan data pasien
                        'value' => true          // Nilai boolean: true / false
                    ]
                ]
            ],

            [
                'key' => 'get_patient',
                'label' => 'Get Patient by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient/{id}',
                'description' => 'Get patient by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_patient_nik',
                'label' => 'Search Patient by NIK',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/nik|{nik}',
                'description' => 'Search patient by NIK',
                'params' => [
                    [
                        'name' => 'nik',
                        'type' => 'text',
                        'placeholder' => 'NIK',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_patient_by_nik_name_birthdate',
                'label' => 'Search Patient (Kombinasi 1: NIK + Nama + Tgl Lahir)',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/nik|{nik}&name={name}&birthdate={birthdate}',
                'description' => 'Mencari data pasien menggunakan kombinasi NIK, Nama, dan Tanggal Lahir (Ketiga parameter ini WAJIB ada bersamaan).',
                'params' => [
                    [
                        'name' => 'nik',
                        'type' => 'text',
                        'placeholder' => 'Masukkan 16 digit NIK pasien',
                        'default' => ''
                    ],
                    [
                        'name' => 'name',
                        'type' => 'text',
                        'placeholder' => 'Nama pasien (contoh: smith)',
                        'default' => ''
                    ],
                    [
                        'name' => 'birthdate',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD (contoh: 1985-10-25)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_patient_by_gender_name_birthdate',
                'label' => 'Search Patient (Kombinasi 2: Gender + Nama + Tgl Lahir)',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient?gender={gender}&name={name}&birthdate={birthdate}',
                'description' => 'Mencari data pasien menggunakan kombinasi Jenis Kelamin, Nama, dan Tanggal Lahir (Ketiga parameter ini WAJIB ada bersamaan).',
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
                        'placeholder' => 'Nama pasien (contoh: budi)',
                        'default' => ''
                    ],
                    [
                        'name' => 'birthdate',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD (contoh: 1990-05-12)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_patient_by_nik_ibu',
                'label' => 'Search Patient (Kombinasi 3: Hanya NIK Ibu)',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/nik-ibu|{nik_ibu}',
                'description' => 'Mencari data pasien (biasanya bayi baru lahir / neonatus) berdasarkan NIK Ibu Kandung yang terdaftar.',
                'params' => [
                    [
                        'name' => 'nik_ibu',
                        'type' => 'text',
                        'placeholder' => 'Masukkan 16 digit NIK Ibu Kandung',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'search_patient_noka',
            //     'label' => 'Search Patient by Noka',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/noka|{noka}',
            //     'description' => 'Search patient by BPJS card number',
            //     'params' => [
            //         [
            //             'name' => 'noka',
            //             'type' => 'text',
            //             'placeholder' => 'Nomor Kartu BPJS',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'update_patient',
                'label' => 'Update Patient (Neonates/WNA Only)',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Patient/{id}',
                'description' => 'Memperbarui data pasien (misal: Bayi yang baru mendapatkan nama tetap atau NIK). Harus mengirimkan data lengkap, bukan parsial.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (e.g., P00012345)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Patient',
                    'id' => '{id}', // ID ini harus sama dengan ID yang ada di URL path
                    'active' => true,
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik',
                            'value' => '3315xxxxxxxxxxxx' // Contoh jika NIK bayi sudah terbit
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Ahmad Fauzi' // Mengubah nama sementara (Bayi Ny. S) menjadi nama asli
                        ]
                    ],
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '08123456789',
                            'use' => 'mobile'
                        ]
                    ],
                    'gender' => 'male',
                    'birthDate' => '2026-05-01',
                    'multipleBirthBoolean' => false,
                    'address' => [
                        [
                            'use' => 'home',
                            'line' => ['Jl. Raya No. 10'],
                            'city' => 'Jepara',
                            'postalCode' => '59411',
                            'country' => 'ID',
                            'extension' => [
                                [
                                    'url' => 'https://fhir.kemkes.go.id/StructureDefinition/administrativeCode',
                                    'valueExtension' => [
                                        ['url' => 'province', 'valueCode' => '33'],
                                        ['url' => 'city', 'valueCode' => '3320'], // Contoh kode wilayah Jepara
                                        ['url' => 'district', 'valueCode' => '3320xx'],
                                        ['url' => 'village', 'valueCode' => '3320xxxxxx']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'extension' => [
                        [
                            'url' => 'https://fhir.kemkes.go.id/StructureDefinition/patient-birthPlace',
                            'valueAddress' => [
                                'city' => 'Jepara',
                                'country' => 'ID'
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'patient_history',
                'label' => 'Patient History',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Patient/{id}/_history',
                'description' => 'Get patient history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

        ]
    ],

    // =====================================================
    // ENCOUNTER
    // =====================================================

    'encounter' => [
        'label' => 'Encounter',
        'icon' => '📅',
        'description' => 'Patient encounters',
        'endpoints' => [

            // ==========================================================
            // 2. ENCOUNTER - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_encounter',
                'label' => 'Create Encounter',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Encounter',
                'description' => 'Mencatatkan data kunjungan atau interaksi klinis baru pasien saat memulai pelayanan di fasilitas kesehatan.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Encounter',
                    'status' => 'arrived', // planned | arrived | triaged | in-progress | onleave | finished | cancelled +
                    'class' => [
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                        'code' => 'AMB', // AMB (ambulatory/rawat jalan), IMP (inpatient/rawat inap), EMER (emergency)
                        'display' => 'ambulatory'
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil dari SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'participant' => [
                        [
                            'type' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                            'code' => 'PPRF',
                                            'display' => 'primary performer'
                                        ]
                                    ]
                                ]
                            ],
                            'individual' => [
                                'reference' => 'Practitioner/N10000001', // ID NIK/SatuSehat Dokter DPJP
                                'display' => 'Nama Dokter Beserta Gelar'
                            ]
                        ]
                    ],
                    'period' => [
                        'start' => '2026-05-29T08:00:00+07:00' // Waktu pasien mulai melakukan registrasi/kedatangan
                    ],
                    'location' => [
                        [
                            'location' => [
                                'reference' => 'Location/b017aa54-f1df-4ec6-b0b4-8bc852dfd112', // ID Location Ruangan/Poli di SATUSEHAT
                                'display' => 'Poliklinik Penyakit Dalam - Ruang 102'
                            ],
                            'status' => 'active'
                        ]
                    ],
                    'statusHistory' => [
                        [
                            'status' => 'arrived',
                            'period' => [
                                'start' => '2026-05-29T08:00:00+07:00'
                            ]
                        ]
                    ],
                    'serviceProvider' => [
                        'reference' => 'Organization/10000004' // ID Organisasi/Faskes (Kode RS/Klinik di SATUSEHAT)
                    ]
                ]
            ],

            // ==========================================================
            // 3. ENCOUNTER - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_encounter',
                'label' => 'Update Encounter',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Encounter/{id}',
                'description' => 'Memperbarui data interaksi kunjungan secara menyeluruh (misal: saat pelayanan selesai atau perubahan status riwayat kunjungan).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Encounter ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Encounter',
                    'id' => '{id}', // WAJIB ada di dalam body payload untuk metode PUT dan nilainya harus sama dengan URL
                    'status' => 'finished', // Skenario pembaruan: Status diubah menjadi finished (selesai pelayanan)
                    'class' => [
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                        'code' => 'AMB',
                        'display' => 'ambulatory'
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'participant' => [
                        [
                            'type' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                            'code' => 'PPRF',
                                            'display' => 'primary performer'
                                        ]
                                    ]
                                ]
                            ],
                            'individual' => [
                                'reference' => 'Practitioner/N10000001',
                                'display' => 'Nama Dokter Beserta Gelar'
                            ]
                        ]
                    ],
                    'period' => [
                        'start' => '2026-05-29T08:00:00+07:00',
                        'end' => '2026-05-29T09:30:00+07:00' // Ditambahkan waktu selesai pelayanan
                    ],
                    'location' => [
                        [
                            'location' => [
                                'reference' => 'Location/b017aa54-f1df-4ec6-b0b4-8bc852dfd112',
                                'display' => 'Poliklinik Penyakit Dalam - Ruang 102'
                            ],
                            'status' => 'completed'
                        ]
                    ],
                    'statusHistory' => [
                        [
                            'status' => 'arrived',
                            'period' => [
                                'start' => '2026-05-29T08:00:00+07:00',
                                'end' => '2026-05-29T08:15:00+07:00'
                            ]
                        ],
                        [
                            'status' => 'in-progress',
                            'period' => [
                                'start' => '2026-05-29T08:15:00+07:00',
                                'end' => '2026-05-29T09:30:00+07:00'
                            ]
                        ],
                        [
                            'status' => 'finished',
                            'period' => [
                                'start' => '2026-05-29T09:30:00+07:00',
                                'end' => '2026-05-29T09:30:00+07:00'
                            ]
                        ]
                    ],
                    'serviceProvider' => [
                        'reference' => 'Organization/10000004'
                    ]
                ]
            ],

            // [
            //     'key' => 'update_encounter_status',
            //     'label' => 'Update Encounter (PUT/PATCH)',
            //     'method' => 'PUT',
            //     'path' => '/fhir-r4/v1/Encounter/{id}',
            //     'description' => 'Mengubah status kunjungan menjadi in-progress atau finished.',
            //     'params' => [
            //         ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Encounter SATUSEHAT', 'default' => '']
            //     ],
            //     'body' => [
            //         'resourceType' => 'Encounter',
            //         'status' => 'finished',
            //         'class' => [
            //             'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
            //             'code' => 'AMB',
            //             'display' => 'ambulatory'
            //         ],
            //         'subject' => [
            //             'reference' => 'Patient/{patient_id}'
            //         ],
            //         'period' => [
            //             'start' => '2026-05-26T09:00:00+07:00',
            //             'end' => '2026-05-26T09:30:00+07:00'
            //         ],
            //         'serviceProvider' => [
            //             'reference' => 'Organization/{org_id}'
            //         ]
            //     ]
            // ],

            // ==========================================================
            // 4. ENCOUNTER - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_encounter',
                'label' => 'Patch Encounter',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Encounter/{id}',
                'description' => 'Memperbarui satu atau beberapa properti rekam kunjungan secara spesifik (parsial) menggunakan format standar JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Encounter ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',
                        'path' => '/status', // Skenario singkat: Mengubah status kunjungan secara parsial tanpa mengirim ulang seluruh objek
                        'value' => 'in-progress'
                    ]
                ],
            ],
            [
                'key' => 'get_encounter',
                'label' => 'Get Encounter by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Encounter/{id}',
                'description' => 'Mengambil detail data kunjungan pasien.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Encounter', 'default' => '']
                ]
            ],

            // ==========================================================
            // 1. ENCOUNTER - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_encounter',
                'label' => 'Search Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Encounter?subject={patient_id}',
                'description' => 'Mencari rekam data kunjungan/interaksi medis pasien berdasarkan ID Pasien (subject) di SATUSEHAT.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ]
                ],
            ],

        ]
    ],

    // =====================================================
    // OBSERVATION
    // =====================================================

    'observation' => [
        'label' => 'Observation',
        'icon' => '📊',
        'description' => 'Observation resources',
        'endpoints' => [

            // ==========================================================
            // 2. OBSERVATION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_observation',
                'label' => 'Create Observation',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Observation',
                'description' => 'Mendaftarkan data temuan klinis, tanda vital, atau hasil pemeriksaan lab baru pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Observation',
                    'status' => 'final', // Status: registered | preliminary | final | amended | corrected | cancelled | entered-in-error | unknown
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                                    'code' => 'vital-signs', // vital-signs = Tanda Vital, laboratory = Hasil Lab, social-history = Merokok/Lifestyle
                                    'display' => 'Vital Signs'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '8867-4', // Contoh kode LOINC untuk Heart rate (Denyut Nadi)
                                'display' => 'Heart rate'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e7eacdf1c38f', // Mengikat ID Encounter kunjungan terkait
                        'display' => 'Kunjungan Pemeriksaan Fisik Terkait'
                    ],
                    'effectiveDateTime' => '2026-05-29T10:00:00+07:00', // Waktu saat pemeriksaan/pengukuran dilakukan
                    'issued' => '2026-05-29T10:05:00+07:00', // Waktu saat data ini dicatat dan dirilis ke sistem
                    'performer' => [
                        [
                            'reference' => 'Practitioner/N10000001', // ID Tenaga Kesehatan / Perawat / Dokter pemeriksa
                            'display' => 'Nama Tenaga Kesehatan Pemeriksa'
                        ]
                    ],
                    'valueQuantity' => [
                        'value' => 80, // Nilai kuantitatif hasil pengukuran
                        'unit' => 'beats/minute',
                        'system' => 'http://unitsofmeasure.org',
                        'code' => '/min' // Kode satuan standar UCUM
                    ]
                ]
            ],
            [
                'key' => 'get_observation',
                'label' => 'Get Observation by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Observation/{id}',
                'description' => 'Mengambil detail data observasi klinis.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'Observation ID', 'default' => '']
                ]
            ],

            [
                'key' => 'search_observation',
                'label' => 'Search Observation by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Observation?subject={patient_id}',
                'description' => 'Search observations',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_observation_by_patient_and_servicerequest',
                'label' => 'Search Observation by Patient & ServiceRequest',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Observation?patient={patient_id}&based-on=ServiceRequest/{servicerequest_id}',
                'description' => 'Mencari data hasil observasi (seperti hasil pemeriksaan Lab/Radiologi) berdasarkan ID Pasien dan ID ServiceRequest (Surat Pengantar/Order). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 10000003)',
                        'default' => ''
                    ],
                    [
                        'name' => 'servicerequest_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (cth: 6694e8c8-052a-4ea6-8072-157b6d47ca08)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. OBSERVATION - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_observation_by_patient_and_encounter_id',
                'label' => 'Search Observation by patiend and encounter id',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Observation?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari rekam temuan klinis atau hasil pemeriksaan (Observation) berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Kunjungan (cth: 4f735a03-128b-464d-bf91-e7eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. OBSERVATION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_observation',
                'label' => 'Update Observation',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Observation/{id}',
                'description' => 'Memperbarui dokumen hasil temuan klinis secara keseluruhan berdasarkan ID Observation. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Observation ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Observation',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'final',
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                                    'code' => 'vital-signs',
                                    'display' => 'Vital Signs'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '8867-4',
                                'display' => 'Heart rate'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e7eacdf1c38f'
                    ],
                    'effectiveDateTime' => '2026-05-29T10:00:00+07:00',
                    'issued' => '2026-05-29T10:10:00+07:00',
                    'performer' => [
                        [
                            'reference' => 'Practitioner/N10000001',
                            'display' => 'Nama Tenaga Kesehatan Pemeriksa'
                        ]
                    ],
                    'valueQuantity' => [
                        'value' => 85, // Skenario pembaruan: Koreksi nilai denyut nadi dari 80 menjadi 85
                        'unit' => 'beats/minute',
                        'system' => 'http://unitsofmeasure.org',
                        'code' => '/min'
                    ]
                ]
            ],

            // ==========================================================
            // 4. OBSERVATION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_observation',
                'label' => 'Patch Observation',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Observation/{id}',
                'description' => 'Memperbarui elemen informasi rekam temuan klinis secara parsial (sebagian) menggunakan format spesifikasi array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Observation ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi penggantian nilai
                        'path' => '/status',     // Jalur elemen target status
                        'value' => 'entered-in-error' // Mengubah status menjadi entered-in-error jika terjadi pembatalan/salah input data tanda vital
                    ]
                ],
            ],

        ]
    ],

    // =====================================================
    // MEDICATION
    // =====================================================

    'medication' => [
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
                            'system' => 'http://sys-ids.kemkes.go.id/medication/10000004', // Menggunakan Kode Fasyankes (Org ID) Anda
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
    ],

    // =====================================================
    // MEDICATION REQUEST
    // =====================================================

    'medicationrequest' => [
        'label' => 'MedicationRequest',
        'icon' => '💊',
        'description' => 'Medication prescriptions and orders',
        'endpoints' => [
            
            // ==========================================================
            // 2. MEDICATION REQUEST - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_medication_request',
                'label' => 'Create MedicationRequest',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/MedicationRequest',
                'description' => 'Mendaftarkan data instruksi peresepan obat baru (resep dokter) ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'MedicationRequest',
                    'status' => 'active', // Status: active | on-hold | cancelled | completed | entered-in-error | stopped | draft | unknown
                    'intent' => 'order', // Intent wajib diisi 'order' untuk peresepan klinis fasyankes
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/medicationrequest-category',
                                    'code' => 'outpatient', // outpatient = Rawat Jalan, inpatient = Rawat Inap, community = Obat Bebas
                                    'display' => 'Outpatient'
                                ]
                            ]
                        ]
                    ],
                    'medicationReference' => [
                        'reference' => 'Medication/4a6d884b-8d4b-4e16-b192-6416502d0999', // Merujuk ke ID resource Medication yang sudah di-POST sebelumnya
                        'display' => 'Amoxicillin 500 mg Kaplet'
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f' // Mengikat ID Encounter kunjungan terkait
                    ],
                    'authoredOn' => '2026-05-29T10:00:00+07:00', // Waktu penulisan resep oleh dokter
                    'requester' => [
                        'reference' => 'Practitioner/N10000001', // ID Dokter DPJP penanggung jawab yang menulis resep
                        'display' => 'Nama Dokter Penulis Resep'
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'A09.9', // Alasan pemberian obat berdasarkan diagnosis ICD-10 (cth: Gastroenteritis)
                                    'display' => 'Gastroenteritis and colitis of unspecified origin'
                                ]
                            ]
                        ]
                    ],
                    'dosageInstruction' => [
                        [
                            'sequence' => 1,
                            'text' => '3 kali sehari 1 kaplet sesudah makan',
                            'additionalInstruction' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://snomed.info/sct',
                                            'code' => '31108002',
                                            'display' => 'With or after food'
                                        ]
                                    ]
                                ]
                            ],
                            'timing' => [
                                'repeat' => [
                                    'frequency' => 3,
                                    'period' => 1,
                                    'periodUnit' => 'd' // d = day (hari)
                                ]
                            ],
                            'route' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-RouteOfAdministration',
                                        'code' => 'PO', // PO = Per Os / Oral (diminum)
                                        'display' => 'Oral'
                                    ]
                                ]
                            ],
                            'doseAndRate' => [
                                [
                                    'type' => [
                                        'coding' => [
                                            [
                                                'system' => 'http://terminology.hl7.org/CodeSystem/dose-rate-type',
                                                'code' => 'ordered',
                                                'display' => 'Ordered'
                                            ]
                                        ]
                                    ],
                                    'doseQuantity' => [
                                        'value' => 1,
                                        'unit' => 'TAB',
                                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-OrderableDrugForm',
                                        'code' => 'TAB'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'dispenseRequest' => [
                        'dispenseInterval' => [
                            'value' => 8,
                            'unit' => 'h',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'h' // Tiap 8 jam
                        ],
                        'validityPeriod' => [
                            'start' => '2026-05-29T10:00:00+07:00',
                            'end' => '2026-06-01T10:00:00+07:00'
                        ],
                        'numberOfRepeatsAllowed' => 0,
                        'quantity' => [
                            'value' => 10, // Total jumlah obat yang diresepkan/diberikan (cth: 10 kaplet)
                            'unit' => 'TAB',
                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-OrderableDrugForm',
                            'code' => 'TAB'
                        ],
                        'expectedSupplyDuration' => [
                            'value' => 3,
                            'unit' => 'd',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'd' // Habis dalam 3 hari
                        ]
                    ]
                ]
            ],
            
            [
                'key' => 'get_medicationrequest',
                'label' => 'Get MedicationRequest by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationRequest/{id}',
                'description' => 'Mengambil detail data instruksi resep.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'MedicationRequest ID', 'default' => '']
                ]
            ],
            [
                'key' => 'search_medicationrequest',
                'label' => 'Search MedicationRequest by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationRequest?subject={patient_id}',
                'description' => 'Search medication requests for a patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. MEDICATION REQUEST - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_medication_request_by_patient_and_encounter',
                'label' => 'Search MedicationRequest By Patiend and Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationRequest?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari rekam medis order peresepan obat (MedicationRequest) berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Kunjungan (cth: 4f735a03-128b-464d-bf91-e7eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. MEDICATION REQUEST - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_medication_request',
                'label' => 'Update MedicationRequest',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/MedicationRequest/{id}',
                'description' => 'Memperbarui dokumen resep obat secara keseluruhan berdasarkan ID MedicationRequest. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationRequest ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'MedicationRequest',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'completed', // Skenario pembaruan: diubah menjadi completed karena obat sudah diserahkan apotek ke pasien
                    'intent' => 'order',
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/medicationrequest-category',
                                    'code' => 'outpatient',
                                    'display' => 'Outpatient'
                                ]
                            ]
                        ]
                    ],
                    'medicationReference' => [
                        'reference' => 'Medication/4a6d884b-8d4b-4e16-b192-6416502d0999',
                        'display' => 'Amoxicillin 500 mg Kaplet'
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f'
                    ],
                    'authoredOn' => '2026-05-29T10:00:00+07:00',
                    'requester' => [
                        'reference' => 'Practitioner/N10000001',
                        'display' => 'Nama Dokter Penulis Resep'
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'A09.9',
                                    'display' => 'Gastroenteritis and colitis of unspecified origin'
                                ]
                            ]
                        ]
                    ],
                    'dosageInstruction' => [
                        [
                            'sequence' => 1,
                            'text' => '3 kali sehari 1 kaplet sesudah makan (Data Terverifikasi)',
                            'additionalInstruction' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://snomed.info/sct',
                                            'code' => '31108002',
                                            'display' => 'With or after food'
                                        ]
                                    ]
                                ]
                            ],
                            'timing' => [
                                'repeat' => [
                                    'frequency' => 3,
                                    'period' => 1,
                                    'periodUnit' => 'd'
                                ]
                            ],
                            'route' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-RouteOfAdministration',
                                        'code' => 'PO',
                                        'display' => 'Oral'
                                    ]
                                ]
                            ],
                            'doseAndRate' => [
                                [
                                    'type' => [
                                        'coding' => [
                                            [
                                                'system' => 'http://terminology.hl7.org/CodeSystem/dose-rate-type',
                                                'code' => 'ordered',
                                                'display' => 'Ordered'
                                            ]
                                        ]
                                    ],
                                    'doseQuantity' => [
                                        'value' => 1,
                                        'unit' => 'TAB',
                                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-OrderableDrugForm',
                                        'code' => 'TAB'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'dispenseRequest' => [
                        'dispenseInterval' => [
                            'value' => 8,
                            'unit' => 'h',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'h'
                        ],
                        'validityPeriod' => [
                            'start' => '2026-05-29T10:00:00+07:00',
                            'end' => '2026-06-01T10:00:00+07:00'
                        ],
                        'numberOfRepeatsAllowed' => 0,
                        'quantity' => [
                            'value' => 10,
                            'unit' => 'TAB',
                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-OrderableDrugForm',
                            'code' => 'TAB'
                        ],
                        'expectedSupplyDuration' => [
                            'value' => 3,
                            'unit' => 'd',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'd'
                        ]
                    ]
                ]
            ],
            
            [
                'key' => 'history_medicationrequest',
                'label' => 'History MedicationRequest',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationRequest/{id}/_history',
                'description' => 'Get medication request history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationRequest ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 4. MEDICATION REQUEST - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_medication_request',
                'label' => 'Patch MedicationRequest',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/MedicationRequest/{id}',
                'description' => 'Memperbarui elemen status resep dokter secara parsial (sebagian) menggunakan format array JSON Patch (misal: menghentikan atau membatalkan resep dengan cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationRequest ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status dokumen resep
                        'value' => 'cancelled'   // Nilai baru, resep dibatalkan (misal karena pasien alergi atau salah input)
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_medicationrequest',
            //     'label' => 'History Type MedicationRequest',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/MedicationRequest/_history',
            //     'description' => 'Get all medication request history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // MEDICATION DISPENSE
    // =====================================================

    'medicationdispense' => [
        'label' => 'MedicationDispense',
        'icon' => '📦',
        'description' => 'Medication dispensing records and fulfillment',
        'endpoints' => [
            [
                'key' => 'search_medicationdispense_by_patient_and_encounter',
                'label' => 'Search MedicationDispense by Patient & Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationDispense?subject={patient_id}&context={encounter_id}',
                'description' => 'Mencari data penyerahan/pemberian obat (MedicationDispense) berdasarkan ID Pasien (subject) dan ID Kunjungan (context). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (cth: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_medicationdispense_by_patient_and_prescription',
                'label' => 'Search MedicationDispense by Patient & Prescription',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationDispense?subject={patient_id}&prescription={prescription_id}',
                'description' => 'Mencari data penyerahan/pemberian obat berdasarkan ID Pasien (subject) dan ID Resep/MedicationRequest (prescription). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'prescription_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (cth: cf92db3e-a044-4e15-83fb-b7ec3a30ba76)',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'create_medicationdispense',
                'label' => 'Create MedicationDispense',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/MedicationDispense',
                'description' => 'Mencatat realisasi penyerahan/peracikan obat oleh instalasi farmasi.',
                'params' => [],
                'body' => [
                    'resourceType' => 'MedicationDispense',
                    'status' => 'completed',
                    'medicationReference' => [
                        'reference' => 'Medication/{medication_id}'
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ],
                    'context' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ],
                    'authorizingPrescription' => [
                        [
                            'reference' => 'MedicationRequest/{medicationrequest_id}'
                        ]
                    ],
                    'quantity' => [
                        'value' => 10,
                        'unit' => 'Tablet'
                    ],
                    'whenHandedOver' => '2026-05-26T09:30:00+07:00'
                ]
            ],
            [
                'key' => 'get_medicationdispense',
                'label' => 'Get MedicationDispense by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationDispense/{id}',
                'description' => 'Mengambil detail data realisasi penyerahan obat.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'MedicationDispense ID', 'default' => '']
                ]
            ],
            // [
            //     'key' => 'create_medicationdispense',
            //     'label' => 'Create MedicationDispense',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/MedicationDispense',
            //     'description' => 'Create new medication dispensing record',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'MedicationDispense',
            //         'status' => 'completed',
            //         'subject' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],
            // [
            //     'key' => 'get_medicationdispense',
            //     'label' => 'Get MedicationDispense by ID',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/MedicationDispense/{id}',
            //     'description' => 'Get medication dispense by ID',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'MedicationDispense ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            [
                'key' => 'search_medicationdispense',
                'label' => 'Search MedicationDispense by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationDispense?subject={patient_id}',
                'description' => 'Search medication dispensing records for a patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'update_medicationdispense',
                'label' => 'Update MedicationDispense',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/MedicationDispense/{id}',
                'description' => 'Update medication dispense',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationDispense ID',
                        'default' => ''
                    ]
                ],
            ],
            // [
            //     'key' => 'delete_medicationdispense',
            //     'label' => 'Delete MedicationDispense',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/MedicationDispense/{id}',
            //     'description' => 'Delete medication dispense',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'MedicationDispense ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            [
                'key' => 'history_medicationdispense',
                'label' => 'History MedicationDispense',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationDispense/{id}/_history',
                'description' => 'Get medication dispense history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationDispense ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'patch_medicationdispense',
                'label' => 'Patch MedicationDispense',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/MedicationDispense/{id}',
                'description' => 'Patch medication dispense',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationDispense ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_medicationdispense',
            //     'label' => 'History Type MedicationDispense',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/MedicationDispense/_history',
            //     'description' => 'Get all medication dispense history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // MEDICATION STATEMENT
    // =====================================================

    'medicationstatement' => [
        'label' => 'MedicationStatement',
        'icon' => '📝',
        'description' => 'Medication statements and patient medication history',
        'endpoints' => [
            [
                'key' => 'create_medicationstatement',
                'label' => 'Create MedicationStatement',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/MedicationStatement',
                'description' => 'Create medication statement',
                'params' => [],
                'body' => [
                    'resourceType' => 'MedicationStatement',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
            [
                'key' => 'get_medicationstatement',
                'label' => 'Get MedicationStatement by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationStatement/{id}',
                'description' => 'Get medication statement by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationStatement ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'search_medicationstatement',
                'label' => 'Search MedicationStatement by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationStatement?subject={patient_id}',
                'description' => 'Search medication statements for a patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'update_medicationstatement',
                'label' => 'Update MedicationStatement',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/MedicationStatement/{id}',
                'description' => 'Update medication statement',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationStatement ID',
                        'default' => ''
                    ]
                ],
            ],
            // [
            //     'key' => 'delete_medicationstatement',
            //     'label' => 'Delete MedicationStatement',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/MedicationStatement/{id}',
            //     'description' => 'Delete medication statement',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'MedicationStatement ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            [
                'key' => 'history_medicationstatement',
                'label' => 'History MedicationStatement',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationStatement/{id}/_history',
                'description' => 'Get medication statement history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationStatement ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'patch_medicationstatement',
                'label' => 'Patch MedicationStatement',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/MedicationStatement/{id}',
                'description' => 'Patch medication statement',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationStatement ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_medicationstatement',
            //     'label' => 'History Type MedicationStatement',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/MedicationStatement/_history',
            //     'description' => 'Get all medication statement history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // SERVICE REQUEST
    // =====================================================

    'servicerequest' => [
        'label' => 'ServiceRequest',
        'icon' => '📨',
        'description' => 'Lab and radiology requests',
        'endpoints' => [

            [
                'key' => 'search_servicerequest_by_patient_and_encounter',
                'label' => 'Search ServiceRequest by Patient & Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ServiceRequest?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari data permintaan layanan (ServiceRequest) berdasarkan ID Pasien (subject) dan ID Kunjungan (encounter). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (cth: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_servicerequest_by_patient_and_accession_number',
                'label' => 'Search ServiceRequest by Accession Number',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ServiceRequest?subject={patient_id}&identifier=http://sys-ids.kemkes.go.id/img-accession-no/{patient_id}|{accession_number}',
                'description' => 'Mencari data order pemeriksaan (seperti Radiologi/Lab) berdasarkan Accession Number spesifik milik pasien.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'accession_number',
                        'type' => 'text',
                        'placeholder' => 'Nomor Aksesi / Order (cth: CR.221005.002)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. SERVICE REQUEST - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_service_request',
                'label' => 'Create ServiceRequest',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/ServiceRequest',
                'description' => 'Mendaftarkan instruksi/permintaan pemeriksaan laboratorium, radiologi, atau tindakan medis baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'ServiceRequest',
                    'identifier' => [
                        [
                            'system' => 'http://sys-ids.kemkes.go.id/servicerequest/10000004', // Menggunakan Kode Fasyankes Anda (Org ID)
                            'value' => 'LAB-20260529-001' // Nomor order/permintaan internal dari SIMRS Anda
                        ]
                    ],
                    'status' => 'active', // Status: draft | active | on-hold | revoked | completed | entered-in-error | unknown
                    'intent' => 'order', // Intent: proposal | plan | directive | order | original-order | reflex-order | filler-order | instance-order | option
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '108252007', // Contoh kode SNOMED CT untuk Laboratory procedure (permintaan lab)
                                    'display' => 'Laboratory procedure'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '11502-2', // Contoh item pemeriksaan: Laboratory report keseluruhan / darah lengkap
                                'display' => 'Laboratory report'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-9617-e98be002cdfa' // Mengikat ID Encounter kunjungan terkait
                    ],
                    'occurrenceDateTime' => '2026-05-29T10:15:00+07:00', // Jadwal rencana pelaksanaan pemeriksaan dilakukan
                    'authoredOn' => '2026-05-29T10:00:00+07:00', // Waktu pembuatan order/instruksi oleh dokter pelapor
                    'requester' => [
                        'reference' => 'Practitioner/N10000001', // ID Dokter DPJP yang meminta pemeriksaan
                        'display' => 'Nama Dokter DPJP Pengirim'
                    ],
                    'performer' => [
                        [
                            'reference' => 'Organization/10000004', // Fasyankes/Laboratorium penanggung jawab pelaksana tindakan
                            'display' => 'Laboratorium Utama RS'
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'D64.9', // Alasan medis permintaan berdasarkan ICD-10 (cth: Anemia, unspecified)
                                    'display' => 'Anemia, unspecified'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'get_service_request',
                'label' => 'Get ServiceRequest',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ServiceRequest/{id}',
                'description' => 'Get service request',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'ServiceRequest ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. SERVICE REQUEST - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_service_request',
                'label' => 'Update ServiceRequest',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/ServiceRequest/{id}',
                'description' => 'Memperbarui dokumen permintaan tindakan secara keseluruhan berdasarkan ID ServiceRequest. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'ServiceRequest ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'ServiceRequest',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'identifier' => [
                        [
                            'system' => 'http://sys-ids.kemkes.go.id/servicerequest/10000004',
                            'value' => 'LAB-20260529-001'
                        ]
                    ],
                    'status' => 'completed', // Skenario pembaruan: status diubah menjadi completed karena laboratorium telah selesai dikerjakan
                    'intent' => 'order',
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '108252007',
                                    'display' => 'Laboratory procedure'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '11502-2',
                                'display' => 'Laboratory report'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-9617-e98be002cdfa'
                    ],
                    'occurrenceDateTime' => '2026-05-29T10:15:00+07:00',
                    'authoredOn' => '2026-05-29T10:00:00+07:00',
                    'requester' => [
                        'reference' => 'Practitioner/N10000001',
                        'display' => 'Nama Dokter DPJP Pengirim'
                    ],
                    'performer' => [
                        [
                            'reference' => 'Organization/10000004',
                            'display' => 'Laboratorium Utama RS'
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'D64.9',
                                    'display' => 'Anemia, unspecified'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. SERVICE REQUEST - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_service_request',
                'label' => 'Patch ServiceRequest',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/ServiceRequest/{id}',
                'description' => 'Memperbarui elemen status dokumen permintaan tindakan medis secara parsial (sebagian) menggunakan skema array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'ServiceRequest ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',      // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',    // Target elemen status dokumen order
                        'value' => 'on-hold'    // Mengubah status menjadi ditunda sementara (on-hold) jika persiapan pasien belum lengkap
                    ]
                ],
            ],

        ]
    ],

    // =====================================================
    // IMMUNIZATION
    // =====================================================

    'immunization' => [
        'label' => 'Immunization',
        'icon' => '💉',
        'description' => 'Immunization records',
        'endpoints' => [

            // Endpoint Baru: Pencarian berdasarkan Patient ID
            [
                'key' => 'search_immunization_by_patient',
                'label' => 'Search Immunization by Patient ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Immunization?patient={patient_id}',
                'description' => 'Mencari dan menampilkan seluruh riwayat imunisasi/vaksinasi spesifik milik satu pasien.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Masukkan ID Patient SATUSEHAT',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'create_immunization',
            //     'label' => 'Create Immunization',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Immunization',
            //     'description' => 'Mencatat pemberian vaksin/imunisasi baru pada pasien sesuai standarisasi profil SATUSEHAT Kemenkes.',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Immunization',
            //         'status' => 'completed',
            //         'vaccineCode' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://sys-ids.kemkes.go.id/kfa',
            //                     'code' => '93001019',
            //                     'display' => 'Vaksin Hepatitis B Rekombinan 0.5 mL'
            //                 ]
            //             ]
            //         ],
            //         'patient' => [
            //             'reference' => 'Patient/{patient_id}',
            //             'display' => 'Nama Pasien'
            //         ],
            //         'encounter' => [
            //             'reference' => 'Encounter/{encounter_id}',
            //             'display' => 'Kunjungan Pemeriksaan'
            //         ],
            //         'occurrenceDateTime' => date('c'),
            //         'primarySource' => true,
            //         'performer' => [
            //             [
            //                 'actor' => [
            //                     'reference' => 'Practitioner/{practitioner_id}',
            //                     'display' => 'Nama Tenaga Kesehatan'
            //                 ]
            //             ]
            //         ],
            //         'reasonCode' => [
            //             [
            //                 'coding' => [
            //                     [
            //                         'system' => 'http://hl7.org/fhir/sid/icd-10',
            //                         'code' => 'Z24.4',
            //                         'display' => 'Need for immunization against viral hepatitis'
            //                     ]
            //                 ]
            //             ]
            //         ],
            //         'protocolApplied' => [
            //             [
            //                 'doseNumberPositiveInt' => 1
            //             ]
            //         ]
            //     ]
            // ],
            [
                'key' => 'get_immunization',
                'label' => 'Get Immunization by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Immunization/{id}',
                'description' => 'Mengambil detail data riwayat imunisasi berdasarkan ID.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'Immunization ID', 'default' => '']
                ]
            ],

            // ==========================================================
            // 1. IMMUNIZATION - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_immunization',
                'label' => 'Search Immunization',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Immunization?patient={patient_id}&date={date}',
                'description' => 'Mencari rekam medis tindakan pemberian vaksin (Immunization) berdasarkan ID Pasien (patient) dan/atau Tanggal Imunisasi (date).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'date',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD (Opsional, cth: 2022-01-11)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. IMMUNIZATION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_immunization',
                'label' => 'Create Immunization',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Immunization',
                'description' => 'Mendaftarkan data riwayat tindakan imunisasi/vaksinasi baru pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Immunization',
                    'status' => 'completed', // Pilihan status: completed | entered-in-error | not-done
                    'vaccineCode' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => 'vg0002', // Contoh Kode KFA rumpun vaksin (misal: Vaksin BCG / Hepatitis B)
                                'display' => 'Vaksin Campak Kering'
                            ]
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001', // Ganti dengan ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29' // Mengikat ID Encounter kunjungan pasien
                    ],
                    'occurrenceDateTime' => '2026-05-29T10:00:00+07:00', // Waktu penyuntikan vaksin dilakukan
                    'primarySource' => true, // Set true jika fasyankes Anda yang langsung menyuntikkan
                    'lotNumber' => 'BATCH-2026-XYZ', // Nomor Batch / Lot dari kemasan fisik vaksin
                    'route' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RouteOfAdministration',
                                'code' => 'IM', // IM = Intramuscular, ID = Intradermal, PO = Per Os (Oral)
                                'display' => 'Injection, intramuscular'
                            ]
                        ]
                    ],
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/N10000001', // ID Dokter/Perawat/Bidan pelaksana vaksinasi
                                'display' => 'Nama Tenaga Kesehatan Vaksinator'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/immunization-reason',
                                    'code' => 'BIAN', // Contoh alasan program pemerintah (cth: BIAN, BIAS, Rutin)
                                    'display' => 'Bulan Imunisasi Anak Nasional'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. IMMUNIZATION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_immunization',
                'label' => 'Update Immunization',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Immunization/{id}',
                'description' => 'Memperbarui data rekam medis imunisasi secara keseluruhan berdasarkan ID Immunization. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Immunization ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Immunization',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'completed',
                    'vaccineCode' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => 'vg0002',
                                'display' => 'Vaksin Campak Kering (Updated)'
                            ]
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29'
                    ],
                    'occurrenceDateTime' => '2026-05-29T10:00:00+07:00',
                    'primarySource' => true,
                    'lotNumber' => 'BATCH-2026-XYZ-KOREKSI', // Contoh pembaruan/koreksi nomor batch vaksin
                    'route' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RouteOfAdministration',
                                'code' => 'IM',
                                'display' => 'Injection, intramuscular'
                            ]
                        ]
                    ],
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/N10000001',
                                'display' => 'Nama Tenaga Kesehatan Vaksinator'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/immunization-reason',
                                    'code' => 'BIAN',
                                    'display' => 'Bulan Imunisasi Anak Nasional'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. IMMUNIZATION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_immunization',
                'label' => 'Patch Immunization',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Immunization/{id}',
                'description' => 'Memperbarui elemen tindakan imunisasi secara parsial (sebagian) menggunakan format array JSON Patch (misalnya merubah status data menjadi entered-in-error jika terjadi kesalahan input).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Immunization ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status tindakan
                        'value' => 'entered-in-error' // Mengubah status menjadi salah input / batal secara cepat
                    ]
                ],
            ],

        ]
    ],

    // =====================================================
    // DIAGNOSTIC REPORT
    // =====================================================

    'diagnosticreport' => [
        'label' => 'DiagnosticReport',
        'icon' => '🧪',
        'description' => 'Diagnostic reports',
        'endpoints' => [

            [
                'key' => 'search_diagnosticreport_by_patient',
                'label' => 'Search DiagnosticReport by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/DiagnosticReport?subject={patient_id}',
                'description' => 'Mencari dokumen laporan hasil pemeriksaan (DiagnosticReport) berdasarkan ID Pasien (subject).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                ],
            ],

            [
                'key' => 'search_diagnosticreport_by_encounter',
                'label' => 'Search DiagnosticReport by Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/DiagnosticReport?encounter={encounter_id}',
                'description' => 'Mencari dokumen laporan hasil pemeriksaan (DiagnosticReport) berdasarkan ID Kunjungan (encounter).',
                'params' => [
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (cth: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_diagnosticreport_by_patient_and_encounter',
                'label' => 'Search DiagnosticReport by Patient & Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/DiagnosticReport?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari dokumen laporan hasil pemeriksaan (DiagnosticReport) berdasarkan ID Pasien (subject) dan ID Kunjungan (encounter). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (cth: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_diagnosticreport_by_patient_and_specimen',
                'label' => 'Search DiagnosticReport by Patient & Specimen',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/DiagnosticReport?subject={patient_id}&specimen={specimen_id}',
                'description' => 'Mencari dokumen laporan hasil pemeriksaan berdasarkan ID Pasien (subject) dan ID Sampel Lab (specimen). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'specimen_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (cth: 5edd0663-093f-40f9-bf04-0c103fd6ec32)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. DIAGNOSTIC REPORT - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_diagnostic_report',
                'label' => 'Create DiagnosticReport',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/DiagnosticReport',
                'description' => 'Mendaftarkan dokumen hasil resmi pemeriksaan laboratorium/radiologi baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'DiagnosticReport',
                    'status' => 'final', // Status: registered | partial | preliminary | final | amended | corrected | entered-in-error
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v2-0074',
                                    'code' => 'LAB', // LAB = Laboratory, RAD = Radiology, MB = Microbiology
                                    'display' => 'Laboratory'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '11502-2', // Contoh kode LOINC untuk Laboratory report
                                'display' => 'Laboratory report'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-9617-e98be002cdfa' // Mengikat ID Encounter kunjungan terkait
                    ],
                    'effectiveDateTime' => '2026-05-29T11:00:00+07:00', // Waktu pengambilan sampel / pelaksanaan tes
                    'issued' => '2026-05-29T13:00:00+07:00', // Waktu laporan resmi ini diterbitkan oleh dokter/analis
                    'performer' => [
                        [
                            'reference' => 'Practitioner/N10000001', // ID Dokter/Nakes pemeriksa laboratorium
                            'display' => 'Nama Dokter Spesialis Patologi Klinik'
                        ]
                    ],
                    'result' => [
                        [
                            'reference' => 'Observation/7d36a3e7-3807-47b8-892c-5b20490df1fa', // Referensi hasil per-item tes dari resource Observation
                            'display' => 'Hemoglobin'
                        ]
                    ],
                    'conclusion' => 'Anemia Ringan (Hasil pemeriksaan darah lengkap menunjukkan penurunan kadar hemoglobin).', // Kesimpulan klinis keseluruhan laporan
                    'conclusionCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '271737000', // Contoh kode SNOMED CT untuk kesimpulan Anemia
                                    'display' => 'Anemia'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. DIAGNOSTIC REPORT - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_diagnostic_report',
                'label' => 'Update DiagnosticReport',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/DiagnosticReport/{id}',
                'description' => 'Memperbarui data dokumen laporan hasil penunjang secara keseluruhan berdasarkan ID DiagnosticReport. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'DiagnosticReport ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'DiagnosticReport',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'corrected', // Skenario pembaruan: status diubah menjadi corrected karena ada perbaikan kesimpulan medis hasil laboratorium
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v2-0074',
                                    'code' => 'LAB',
                                    'display' => 'Laboratory'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '11502-2',
                                'display' => 'Laboratory report'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-9617-e98be002cdfa'
                    ],
                    'effectiveDateTime' => '2026-05-29T11:00:00+07:00',
                    'issued' => '2026-05-29T13:30:00+07:00', // Jam rilis revisi laporan
                    'performer' => [
                        [
                            'reference' => 'Practitioner/N10000001',
                            'display' => 'Nama Dokter Spesialis Patologi Klinik'
                        ]
                    ],
                    'result' => [
                        [
                            'reference' => 'Observation/7d36a3e7-3807-47b8-892c-5b20490df1fa',
                            'display' => 'Hemoglobin'
                        ]
                    ],
                    'conclusion' => 'Kadar hemoglobin dalam batas normal setelah konfirmasi ulang sampel darah.', // Teks kesimpulan yang telah diperbaiki
                    'conclusionCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '102445001', // Kode SNOMED CT untuk normal
                                    'display' => 'Normal'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. DIAGNOSTIC REPORT - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_diagnostic_report',
                'label' => 'Patch DiagnosticReport',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/DiagnosticReport/{id}',
                'description' => 'Memperbarui elemen status atau rincian dokumen laporan penunjang secara parsial (sebagian) menggunakan skema array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'DiagnosticReport ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',           // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',         // Target elemen status
                        'value' => 'entered-in-error' // Mengubah status menjadi entered-in-error secara instan jika ada kesalahan rilis dokumen laporan
                    ]
                ],
            ],

        ]
    ],

    // =====================================================
    // PROCEDURE
    // =====================================================

    'procedure' => [
        'label' => 'Procedure',
        'icon' => '🔪',
        'description' => 'Procedure records and surgical interventions',
        'endpoints' => [

            // [
            //     'key' => 'create_procedure',
            //     'label' => 'Create Procedure',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Procedure',
            //     'description' => 'Create procedure',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Procedure',
            //         'status' => 'completed',
            //         'code' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
            //                     'code' => '',
            //                     'display' => ''
            //                 ]
            //             ]
            //         ],
            //         'subject' => [
            //             'reference' => 'Patient/{patient_id}'
            //         ]
            //     ]
            // ],

            [
                'key' => 'get_procedure',
                'label' => 'Get Procedure by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure/{id}',
                'description' => 'Get procedure by SATUSEHAT ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Procedure ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_procedure',
                'label' => 'Search Procedure by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure?subject={patient_id}',
                'description' => 'Search procedures for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_procedure_by_encounter',
                'label' => 'Search Procedure by Encounter ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure?encounter={encounter_id}',
                'description' => 'Mencari semua data tindakan medis (Procedure) yang dilakukan selama satu kunjungan (Encounter) tertentu.',
                'params' => [
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (contoh: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'update_procedure',
            //     'label' => 'Update Procedure',
            //     'method' => 'PUT',
            //     'path' => '/fhir-r4/v1/Procedure/{id}',
            //     'description' => 'Update procedure',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Procedure ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            // [
            //     'key' => 'patch_procedure',
            //     'label' => 'Patch Procedure',
            //     'method' => 'PATCH',
            //     'path' => '/fhir-r4/v1/Procedure/{id}',
            //     'description' => 'Patch procedure',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Procedure ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            // [
            //     'key' => 'delete_procedure',
            //     'label' => 'Delete Procedure',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/Procedure/{id}',
            //     'description' => 'Delete procedure',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Procedure ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_procedure',
                'label' => 'History Procedure',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure/{id}/_history',
                'description' => 'Get procedure history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Procedure ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. PROCEDURE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_procedure_by_patiend_id_and_encounter_id',
                'label' => 'Search Procedure by Patiend Id and Encounter Id',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Procedure?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari rekam medis tindakan/prosedur (Procedure) berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Kunjungan (cth: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. PROCEDURE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_procedure',
                'label' => 'Create Procedure',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Procedure',
                'description' => 'Mendaftarkan data rekam medis tindakan atau prosedur klinis baru yang telah dilakukan kepada pasien.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Procedure',
                    'status' => 'completed', // Status tindakan: preparation | in-progress | not-done | on-hold | completed | entered-in-error | unknown
                    'category' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '409073007', // Contoh kode kategori SNOMED CT: Surgical procedure
                                'display' => 'Surgical procedure'
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
                                'code' => '89.52', // Contoh tindakan berdasarkan ICD-9-CM (cth: Electrocardiogram / EKG)
                                'display' => 'Electrocardiogram'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f', // Mengikat ID Encounter kunjungan terkait
                        'display' => 'Kunjungan Rawat Jalan/Inap Terkait'
                    ],
                    'performedPeriod' => [
                        'start' => '2026-05-29T10:00:00+07:00', // Waktu tindakan dimulai
                        'end' => '2026-05-29T10:30:00+07:00'    // Waktu tindakan selesai
                    ],
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/N10000001', // ID Dokter DPJP / Nakes yang melakukan tindakan
                                'display' => 'Nama Dokter Pelaksana Tindakan'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'I21.9', // Alasan medis dilakukan tindakan (ICD-10)
                                    'display' => 'Acute myocardial infarction, unspecified'
                                ]
                            ]
                        ]
                    ],
                    'note' => [
                        [
                            'text' => 'Hasil perekaman EKG menunjukkan adanya ST-elevasi ringan, tindakan berjalan lancar tanpa komplikasi.' // Catatan klinis tambahan pelaksana tindakan
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. PROCEDURE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_procedure',
                'label' => 'Update Procedure',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Procedure/{id}',
                'description' => 'Memperbarui dokumen rekaman tindakan medis secara keseluruhan berdasarkan ID Procedure. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Procedure ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Procedure',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'completed',
                    'category' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '409073007',
                                'display' => 'Surgical procedure'
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
                                'code' => '89.52',
                                'display' => 'Electrocardiogram (Data Terupdate)'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f',
                        'display' => 'Kunjungan Rawat Jalan/Inap Terkait'
                    ],
                    'performedPeriod' => [
                        'start' => '2026-05-29T10:00:00+07:00',
                        'end' => '2026-05-29T10:30:00+07:00'
                    ],
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/N10000001',
                                'display' => 'Nama Dokter Pelaksana Tindakan'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'I21.9',
                                    'display' => 'Acute myocardial infarction, unspecified'
                                ]
                            ]
                        ]
                    ],
                    'note' => [
                        [
                            'text' => 'Hasil perekaman EKG terverifikasi ulang oleh DPJP.'
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. PROCEDURE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_procedure',
                'label' => 'Patch Procedure',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Procedure/{id}',
                'description' => 'Memperbarui elemen atau status rekaman tindakan secara parsial menggunakan format spesifikasi array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Procedure ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi penggantian nilai
                        'path' => '/status',     // Jalur elemen target (misal mengubah status tindakan)
                        'value' => 'entered-in-error' // Mengubah status menjadi entered-in-error jika terjadi salah input tindakan pada SIMRS
                    ]
                ],
            ],

        ]
    ],

    // =====================================================
    // SPECIMEN
    // =====================================================

    'specimen' => [
        'label' => 'Specimen',
        'icon' => '🧪',
        'description' => 'Specimen resources for lab tests and diagnostics',
        'endpoints' => [
            [
                'key' => 'search_specimen_by_patient_and_collector',
                'label' => 'Search Specimen by Patient & Collector',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen?subject={patient_id}&collector={collector_id}',
                'description' => 'Mencari data sampel (Specimen) berdasarkan ID Pasien (subject) dan ID Tenaga Kesehatan pengambil sampel (collector). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'collector_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Practitioner ID Nakes (cth: N10000001)',
                        'default' => ''
                    ]
                ],
            ],
    
            [
                'key' => 'search_specimen_by_patient_and_collected_date',
                'label' => 'Search Specimen by Patient & Collected Date',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen?subject={patient_id}&collected={collected_date}',
                'description' => 'Mencari data sampel berdasarkan ID Pasien (subject) dan tanggal pengambilan sampel (collected). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'collected_date',
                        'type' => 'text',
                        'placeholder' => 'Format: YYYY-MM-DD (cth: 2022-06-28)',
                        'default' => ''
                    ]
                ],
            ],
        
            // ==========================================================
            // 2. SPECIMEN - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_specimen',
                'label' => 'Create Specimen',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Specimen',
                'description' => 'Mendaftarkan data pengambilan sampel baru pasien untuk keperluan pemeriksaan laboratorium ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Specimen',
                    'status' => 'available', // Status sampel: available | unavailable | unsatisfactory | entered-in-error
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '119297000', // Contoh kode SNOMED CT untuk spesimen darah lengkap (Blood specimen)
                                'display' => 'Blood specimen'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'request' => [
                        [
                            'reference' => 'ServiceRequest/6f4e3c2a-9617-4dfc-bccc-98ea002cdfa9' // Merujuk ke ID ServiceRequest (Order Permintaan Lab)
                        ]
                    ],
                    'collection' => [
                        'collector' => [
                            'reference' => 'Practitioner/N10000001', // ID Tenaga Kesehatan / Analis yang mengambil sampel
                            'display' => 'Nama Analis Laboratorium'
                        ],
                        'collectedDateTime' => '2026-05-29T10:00:00+07:00', // Waktu pengambilan sampel dilakukan
                        'quantity' => [
                            'value' => 3,
                            'unit' => 'mL',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'mL'
                        ],
                        'bodySite' => [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '49852007', // Contoh kode SNOMED CT lokasi tubuh (cth: Median cubital vein / Vena fossa cubiti)
                                    'display' => 'Structure of median cubital vein'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'get_specimen',
                'label' => 'Get Specimen by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen/{id}',
                'description' => 'Get specimen by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. SPECIMEN - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_specimen',
                'label' => 'Search Specimen',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen?subject={patient_id}',
                'description' => 'Mencari data rekam riwayat pengambilan sampel klinis (Specimen) berdasarkan ID Pasien (subject).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. SPECIMEN - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_specimen',
                'label' => 'Update Specimen',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Specimen/{id}',
                'description' => 'Memperbarui rekam data spesimen secara keseluruhan berdasarkan ID Specimen. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Specimen',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'available',
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '119297000',
                                'display' => 'Blood specimen'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'request' => [
                        [
                            'reference' => 'ServiceRequest/6f4e3c2a-9617-4dfc-bccc-98ea002cdfa9'
                        ]
                    ],
                    'collection' => [
                        'collector' => [
                            'reference' => 'Practitioner/N10000001',
                            'display' => 'Nama Analis Laboratorium'
                        ],
                        'collectedDateTime' => '2026-05-29T10:00:00+07:00',
                        'quantity' => [
                            'value' => 5, // Contoh pembaruan data: Penyesuaian volume spesimen menjadi 5 mL
                            'unit' => 'mL',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'mL'
                        ],
                        'bodySite' => [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '49852007',
                                    'display' => 'Structure of median cubital vein'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. SPECIMEN - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_specimen',
                'label' => 'Patch Specimen',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Specimen/{id}',
                'description' => 'Memperbarui elemen informasi data sampel biologis secara parsial (sebagian) menggunakan format array JSON Patch (misalnya merubah status ketersediaan sampel dengan cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status spesimen
                        'value' => 'unsatisfactory' // Contoh perubahan status parsial: Sampel rusak / lisis sehingga tidak layak uji lab
                    ]
                ],
            ],

            // [
            //     'key' => 'delete_specimen',
            //     'label' => 'Delete Specimen',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/Specimen/{id}',
            //     'description' => 'Delete specimen',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Specimen ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_specimen',
                'label' => 'History Specimen',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Specimen/{id}/_history',
                'description' => 'Get specimen history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'patch_specimen',
                'label' => 'Patch Specimen',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Specimen/{id}',
                'description' => 'Patch specimen',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Specimen ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_specimen',
            //     'label' => 'History Type Specimen',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Specimen/_history',
            //     'description' => 'Get all specimen history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // IMAGING STUDY
    // =====================================================

    'imagingstudy' => [
        'label' => 'ImagingStudy',
        'icon' => '🩻',
        'description' => 'Imaging studies',
        'endpoints' => [

            [
                'key' => 'search_imaging_study_by_patient',
                'label' => 'Search ImagingStudy by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ImagingStudy?patient={patient_id}',
                'description' => 'Search imaging study by patient Id',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. IMAGING STUDY - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_imaging_study',
                'label' => 'Search ImagingStudy',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ImagingStudy?identifier=http://sys-ids.kemkes.go.id/acsn/{patient_id}|{accession_number}',
                'description' => 'Mencari data hasil pemeriksaan radiologi (ImagingStudy) berdasarkan nomor aksesi (accession number) dan ID Pasien (subject). Parameter identifier ini bersifat WAJIB.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'accession_number',
                        'type' => 'text',
                        'placeholder' => 'Nomor Aksesi Radiologi SIMRS (cth: ACC-2026-0001)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. IMAGING STUDY - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_imaging_study',
                'label' => 'Create ImagingStudy',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/ImagingStudy',
                'description' => 'Mendaftarkan rekam medis pemeriksaan citra radiologi (ImagingStudy) baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'ImagingStudy',
                    'status' => 'available', // Pilihan status: registered | available | cancelled | entered-in-error | unknown
                    'subject' => [
                        'reference' => 'Patient/100000000001', // Ganti dengan ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29' // Mengikat ID Encounter kunjungan pasien
                    ],
                    'started' => '2026-05-29T17:00:00+07:00', // Waktu dimulainya pemeriksaan pencitraan
                    'author' => [
                        [
                            'reference' => 'Practitioner/N10000001', // ID Dokter Pengirim / Radiolog
                            'display' => 'Nama Dokter Spesialis Radiologi'
                        ]
                    ],
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'http://sys-ids.kemkes.go.id/acsn/100000000001', // Menggunakan ID Pasien di dalam URL system
                            'value' => 'ACC-2026-0001' // Nomor Aksesi / Kode Pemeriksaan Radiologi lokal
                        ]
                    ],
                    'modality' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://dicom.nema.org/resources/ontology/DCM',
                                    'code' => 'DX', // DX = Digital Radiography (Rontgen Dada biasa), CT = Computed Tomography, US = Ultrasound
                                    'display' => 'Digital Radiography'
                                ]
                            ]
                        ]
                    ],
                    'description' => 'Pemeriksaan Thorax AP/PA',
                    'series' => [
                        [
                            'uid' => '1.2.840.113619.2.134.1.20260529.123456', // DICOM Series Instance UID unik
                            'number' => 1,
                            'modality' => [
                                'system' => 'http://dicom.nema.org/resources/ontology/DCM',
                                'code' => 'DX',
                                'display' => 'Digital Radiography'
                            ],
                            'description' => 'Thorax View',
                            'instance' => [
                                [
                                    'uid' => '1.2.840.113619.2.134.1.20260529.123456.1', // DICOM SOP Instance UID objek gambar
                                    'sopClass' => [
                                        'system' => 'urn:ietf:rfc:3986',
                                        'code' => 'urn:oid:1.2.840.10008.5.1.4.1.1.1' // SOP Class UID standar untuk Digital X-Ray Image
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. IMAGING STUDY - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_imaging_study',
                'label' => 'Update ImagingStudy',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/ImagingStudy/{id}',
                'description' => 'Memperbarui keseluruhan dokumen pencitraan medis radiologi berdasarkan ID ImagingStudy. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'ImagingStudy ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'ImagingStudy',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'available',
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29'
                    ],
                    'started' => '2026-05-29T17:00:00+07:00',
                    'author' => [
                        [
                            'reference' => 'Practitioner/N10000001',
                            'display' => 'Nama Dokter Spesialis Radiologi'
                        ]
                    ],
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'http://sys-ids.kemkes.go.id/acsn/100000000001',
                            'value' => 'ACC-2026-0001'
                        ]
                    ],
                    'modality' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://dicom.nema.org/resources/ontology/DCM',
                                    'code' => 'DX',
                                    'display' => 'Digital Radiography (Updated)'
                                ]
                            ]
                        ]
                    ],
                    'description' => 'Pemeriksaan Thorax AP/PA (Data Terkoreksi)',
                    'series' => [
                        [
                            'uid' => '1.2.840.113619.2.134.1.20260529.123456',
                            'number' => 1,
                            'modality' => [
                                'system' => 'http://dicom.nema.org/resources/ontology/DCM',
                                'code' => 'DX',
                                'display' => 'Digital Radiography'
                            ],
                            'description' => 'Thorax View',
                            'instance' => [
                                [
                                    'uid' => '1.2.840.113619.2.134.1.20260529.123456.1',
                                    'sopClass' => [
                                        'system' => 'urn:ietf:rfc:3986',
                                        'code' => 'urn:oid:1.2.840.10008.5.1.4.1.1.1'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

        ]
    ],

    // =====================================================
    // APPOINTMENT
    // =====================================================

    'appointment' => [
        'label' => 'Appointment',
        'icon' => '🗓️',
        'description' => 'Appointments',
        'endpoints' => [

            // ==========================================================
            // 2. APPOINTMENT - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_appointment',
                'label' => 'Create Appointment',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Appointment',
                'description' => 'Mendaftarkan data pemesanan jadwal janji temu kunjungan baru pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Appointment',
                    'status' => 'booked', // Status: proposed | pending | booked | arrived | fulfilled | cancelled | noshow | entered-in-error
                    'appointmentType' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v4-0276',
                                'code' => 'ROUTINE', // Kunjungan Rutin / Biasa
                                'display' => 'Routine visit'
                            ]
                        ]
                    ],
                    'serviceCategory' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/service-category',
                                    'code' => '17', // 17 = General Practice / Umum (Sesuaikan dengan rumpun poliklinik)
                                    'display' => 'General Practice'
                                ]
                            ]
                        ]
                    ],
                    'specialty' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/clinical-specialty',
                                    'code' => 'S001.09', // Kode spesialisasi rujukan lokal Kemenkes (cth: Anak)
                                    'display' => 'Pediatrie / Anak'
                                ]
                            ]
                        ]
                    ],
                    'slot' => [
                        [
                            'reference' => 'Slot/e2fdfc6f-28ff-46be-b68b-b73d982cdcf8' // Menandai blok celah waktu Slot yang di-booking
                        ]
                    ],
                    'start' => '2026-06-01T09:00:00+07:00', // Estimasi waktu mulai janji temu
                    'end' => '2026-06-01T09:15:00+07:00',   // Estimasi waktu selesai janji temu
                    'created' => '2026-05-29T18:00:00+07:00', // Waktu booking ini dibuat oleh sistem rumah sakit
                    'comment' => 'Pendaftaran antrean pemeriksaan rutin anak via Mobile JKN / Aplikasi SIMRS',
                    'participant' => [
                        [
                            'actor' => [
                                'reference' => 'Patient/100000000001', // Referensi pasien yang berobat
                                'display' => 'Nama Pasien Sesuai KTP'
                            ],
                            'status' => 'accepted', // Status partisipasi pasien
                            'required' => 'required'
                        ],
                        [
                            'actor' => [
                                'reference' => 'Practitioner/N10000001', // Referensi dokter pemeriksa
                                'display' => 'Nama Dokter Spesialis Anak'
                            ],
                            'status' => 'accepted',
                            'required' => 'required'
                        ],
                        [
                            'actor' => [
                                'reference' => 'Location/b016428c-4f1e-4503-a12b-3a3d582cdcf8', // Ruangan Poliklinik tujuan
                                'display' => 'Poliklinik Anak Lantai 2'
                            ],
                            'status' => 'accepted',
                            'required' => 'required'
                        ]
                    ]
                ]
            ],

            [
                'key' => 'get_appointment',
                'label' => 'Get Appointment by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Appointment/{id}',
                'description' => 'Get appointment by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Appointment ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_appointment',
                'label' => 'Search Appointment by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Appointment?patient={patient_id}',
                'description' => 'Search appointments for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. APPOINTMENT - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_appointment_by_service_id',
                'label' => 'Search Appointment by Service Id',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Appointment?actor={healthcare_service_id}',
                'description' => 'Mencari data pemesanan janji temu (Appointment) berdasarkan ID aktor Pelayanan Kesehatan (HealthcareService).',
                'params' => [
                    [
                        'name' => 'healthcare_service_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID HealthcareService (cth: 16e8ab09-0c07-4486-ad7e-b708e6fafb2a)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. APPOINTMENT - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_appointment',
                'label' => 'Update Appointment',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Appointment/{id}',
                'description' => 'Memperbarui data janji temu secara keseluruhan berdasarkan ID Appointment. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Appointment ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Appointment',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'fulfilled', // Skenario pembaruan: diubah menjadi fulfilled karena pelayanan dokter telah selesai terlaksana
                    'appointmentType' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v4-0276',
                                'code' => 'ROUTINE',
                                'display' => 'Routine visit'
                            ]
                        ]
                    ],
                    'serviceCategory' => [
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
                    'slot' => [
                        [
                            'reference' => 'Slot/e2fdfc6f-28ff-46be-b68b-b73d982cdcf8'
                        ]
                    ],
                    'start' => '2026-06-01T09:00:00+07:00',
                    'end' => '2026-06-01T09:15:00+07:00',
                    'created' => '2026-05-29T18:00:00+07:00',
                    'comment' => 'Pelayanan Konsultasi Selesai Dilakukan',
                    'participant' => [
                        [
                            'actor' => [
                                'reference' => 'Patient/100000000001',
                                'display' => 'Nama Pasien Sesuai KTP'
                            ],
                            'status' => 'accepted',
                            'required' => 'required'
                        ],
                        [
                            'actor' => [
                                'reference' => 'Practitioner/N10000001',
                                'display' => 'Nama Dokter Spesialis Anak'
                            ],
                            'status' => 'accepted',
                            'required' => 'required'
                        ],
                        [
                            'actor' => [
                                'reference' => 'Location/b016428c-4f1e-4503-a12b-3a3d582cdcf8',
                                'display' => 'Poliklinik Anak Lantai 2'
                            ],
                            'status' => 'accepted',
                            'required' => 'required'
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. APPOINTMENT - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_appointment',
                'label' => 'Patch Appointment',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Appointment/{id}',
                'description' => 'Memperbarui status elemen janji temu secara parsial (sebagian) menggunakan format array JSON Patch (sangat ideal untuk membatalkan antrean dengan merubah status menjadi cancelled).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Appointment ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status janji temu
                        'value' => 'cancelled'   // Mengubah status menjadi dibatalkan secara instan jika pasien membatalkan antrean
                    ]
                ],
            ],

            // [
            //     'key' => 'delete_appointment',
            //     'label' => 'Delete Appointment',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/Appointment/{id}',
            //     'description' => 'Delete appointment',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Appointment ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_appointment',
                'label' => 'History Appointment',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Appointment/{id}/_history',
                'description' => 'Get appointment history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Appointment ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_appointment',
            //     'label' => 'History Type Appointment',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Appointment/_history',
            //     'description' => 'Get all appointment history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // CARE PLAN
    // =====================================================

    'careplan' => [
        'label' => 'CarePlan',
        'icon' => '📝',
        'description' => 'Care plans',
        'endpoints' => [

            [
                'key' => 'create_careplan',
                'label' => 'Create CarePlan',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/CarePlan',
                'description' => 'Create care plan',
                'params' => [],
                'body' => [
                    'resourceType' => 'CarePlan',
                    'status' => 'active',
                    'intent' => 'plan',

                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
            ],

            [
                'key' => 'get_careplan',
                'label' => 'Get CarePlan by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CarePlan/{id}',
                'description' => 'Get care plan by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CarePlan ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_careplan',
                'label' => 'Search CarePlan by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CarePlan?subject={patient_id}',
                'description' => 'Search care plans for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'update_careplan',
                'label' => 'Update CarePlan',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/CarePlan/{id}',
                'description' => 'Fungsi dari ReST API ini adalah untuk melakukan perubahan data (update) terkait resource CarePlan secara keseluruhan.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CarePlan ID',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'CarePlan'
                ]
            ],
            
            [
                'key' => 'patch_careplan',
                'label' => 'Patch CarePlan',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/CarePlan/{id}',
                'description' => 'Fungsi dari ReST API ini adalah untuk melakukan perubahan sebagian dari data terkait resource CarePlan (patching).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CarePlan ID',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',
                        'path' => '/language',
                        'value' => 'id'
                    ]
                ]
            ],

            // [
            //     'key' => 'delete_careplan',
            //     'label' => 'Delete CarePlan',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/CarePlan/{id}',
            //     'description' => 'Delete care plan',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'CarePlan ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_careplan',
                'label' => 'History CarePlan',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CarePlan/{id}/_history',
                'description' => 'Get care plan history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CarePlan ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_careplan',
            //     'label' => 'History Type CarePlan',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/CarePlan/_history',
            //     'description' => 'Get all care plan history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // DOCUMENT REFERENCE
    // =====================================================

    'documentreference' => [
        'label' => 'DocumentReference',
        'icon' => '📄',
        'description' => 'Document references',
        'endpoints' => [

            [
                'key' => 'create_document_reference',
                'label' => 'Create DocumentReference',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/DocumentReference',
                'description' => 'Create document reference',
                'params' => [],
                'body' => [
                    'resourceType' => 'DocumentReference',
                    'status' => 'current',

                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ],

                    'content' => [
                        [
                            'attachment' => [
                                'contentType' => 'application/pdf',
                                'url' => ''
                            ]
                        ]
                    ]
                ]
            ],

        ]
    ],

    // =====================================================
    // CONSENT
    // =====================================================

    'consent' => [
        'label' => 'Consent',
        'icon' => '✅',
        'description' => 'Patient consent',
        'endpoints' => [

            [
                'key' => 'create_consent',
                'label' => 'Create Consent',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Consent',
                'description' => 'Create consent',
                'params' => [],
                'body' => [
                    'resourceType' => 'Consent',
                    'status' => 'active',

                    'patient' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
            ],

            [
                'key' => 'get_consent',
                'label' => 'Get Consent by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Consent/{id}',
                'description' => 'Get consent by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Consent ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_consent',
                'label' => 'Search Consent by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Consent?patient={patient_id}',
                'description' => 'Search consents for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'update_consent',
                'label' => 'Update Consent',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Consent/{id}',
                'description' => 'Update consent',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Consent ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'delete_consent',
                'label' => 'Delete Consent',
                'method' => 'DELETE',
                'path' => '/fhir-r4/v1/Consent/{id}',
                'description' => 'Delete consent',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Consent ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'history_consent',
                'label' => 'History Consent',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Consent/{id}/_history',
                'description' => 'Get consent history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Consent ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_consent',
            //     'label' => 'History Type Consent',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Consent/_history',
            //     'description' => 'Get all consent history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // COVERAGE
    // =====================================================

    'coverage' => [
        'label' => 'Coverage',
        'icon' => '🛡️',
        'description' => 'Insurance coverage',
        'endpoints' => [

            [
                'key' => 'create_coverage',
                'label' => 'Create Coverage',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Coverage',
                'description' => 'Create coverage',
                'params' => [],
                'body' => [
                    'resourceType' => 'Coverage',
                    'status' => 'active',

                    'beneficiary' => [
                        'reference' => 'Patient/{patient_id}'
                    ],

                    'subscriberId' => ''
                ]
            ],

            [
                'key' => 'get_coverage',
                'label' => 'Get Coverage by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Coverage/{id}',
                'description' => 'Get coverage by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Coverage ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_coverage',
                'label' => 'Search Coverage by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Coverage?beneficiary={patient_id}',
                'description' => 'Search coverage for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'update_coverage',
                'label' => 'Update Coverage',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Coverage/{id}',
                'description' => 'Update coverage',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Coverage ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'delete_coverage',
                'label' => 'Delete Coverage',
                'method' => 'DELETE',
                'path' => '/fhir-r4/v1/Coverage/{id}',
                'description' => 'Delete coverage',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Coverage ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'history_coverage',
                'label' => 'History Coverage',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Coverage/{id}/_history',
                'description' => 'Get coverage history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Coverage ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_coverage',
            //     'label' => 'History Type Coverage',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Coverage/_history',
            //     'description' => 'Get all coverage history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // ORGANIZATION
    // =====================================================

    'organization' => [
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
    ],

    // =====================================================
    // LOCATION
    // =====================================================

    'location' => [
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
                            'system' => 'http://sys-ids.kemkes.go.id/location/100028344', // ID Fasyankes Utama Anda
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
                        'reference' => 'Organization/100028344' // ID Organisasi pengelola
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
    ],

    // =====================================================
    // PRACTITIONER
    // =====================================================

    'practitioner' => [
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
    ],

    // =====================================================
    // EPISODE OF CARE
    // =====================================================

    'episodeofcare' => [
        'label' => 'EpisodeOfCare',
        'icon' => '🔄',
        'description' => 'Episode of care',
        'endpoints' => [

            // ==========================================================
            // 2. EPISODE OF CARE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_episode_of_care',
                'label' => 'Create EpisodeOfCare',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/EpisodeOfCare',
                'description' => 'Mendaftarkan data asuhan perawatan berkelanjutan baru untuk program kesehatan spesifik pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'EpisodeOfCare',
                    'status' => 'active', // Status program: planned | waitlist | active | onhold | finished | cancelled | entered-in-error
                    'statusHistory' => [
                        [
                            'status' => 'active',
                            'period' => [
                                'start' => '2026-05-29T08:00:00+07:00' // Riwayat pencatatan waktu status aktif dimulai
                            ]
                        ]
                    ],
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/episodeofcare-type',
                                    'code' => 'TB', // Contoh kode jenis manajemen program kesehatan (cth: Tuberkulosis)
                                    'display' => 'Tuberkulosis'
                                ]
                            ]
                        ]
                    ],
                    'diagnosis' => [
                        [
                            'condition' => [
                                'reference' => 'Condition/f24ad72a-14ba-46e3-982c-7b04901fa093', // ID Diagnosis dasar dari resource Condition
                                'display' => 'Tuberculosis of lung, bacteriologically and histologically confirmed'
                            ],
                            'role' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
                                        'code' => 'CC', // CC = Chief Complaint / Diagnosis Utama Rangkaian Asuhan
                                        'display' => 'Chief complaint'
                                    ]
                                ]
                            ],
                            'rank' => 1
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'managingOrganization' => [
                        'reference' => 'Organization/10000004' // ID Organisasi/Fasyankes penanggung jawab program asuhan
                    ],
                    'period' => [
                        'start' => '2026-05-29T08:00:00+07:00' // Rentang waktu asuhan keperawatan dimulai
                    ],
                    'careManager' => [
                        'reference' => 'Practitioner/N10000001', // Dokter penanggung jawab kasus (DPJP) utama
                        'display' => 'Nama Dokter DPJP Utama'
                    ]
                ]
            ],

            [
                'key' => 'get_episode_of_care',
                'label' => 'Get EpisodeOfCare by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/EpisodeOfCare/{id}',
                'description' => 'Get episode of care by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. EPISODE OF CARE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_episode_of_care',
                'label' => 'Search EpisodeOfCare',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/EpisodeOfCare?subject={patient_id}',
                'description' => 'Mencari data rekam rangkaian asuhan perawatan berkelanjutan (EpisodeOfCare) berdasarkan ID Pasien (subject).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. EPISODE OF CARE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_episode_of_care',
                'label' => 'Update EpisodeOfCare',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/EpisodeOfCare/{id}',
                'description' => 'Memperbarui rekam data program asuhan berkelanjutan secara keseluruhan berdasarkan ID EpisodeOfCare. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'EpisodeOfCare',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'finished', // Skenario pembaruan: status diubah menjadi finished karena rangkaian terapi/asuhan telah selesai
                    'statusHistory' => [
                        [
                            'status' => 'active',
                            'period' => [
                                'start' => '2026-05-29T08:00:00+07:00',
                                'end' => '2026-05-29T10:00:00+07:00'
                            ]
                        ],
                        [
                            'status' => 'finished',
                            'period' => [
                                'start' => '2026-05-29T10:00:00+07:00'
                            ]
                        ]
                    ],
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/episodeofcare-type',
                                    'code' => 'TB',
                                    'display' => 'Tuberkulosis'
                                ]
                            ]
                        ]
                    ],
                    'diagnosis' => [
                        [
                            'condition' => [
                                'reference' => 'Condition/f24ad72a-14ba-46e3-982c-7b04901fa093',
                                'display' => 'Tuberculosis of lung, bacteriologically and histologically confirmed'
                            ],
                            'role' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
                                        'code' => 'CC',
                                        'display' => 'Chief complaint'
                                    ]
                                ]
                            ],
                            'rank' => 1
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'managingOrganization' => [
                        'reference' => 'Organization/10000004'
                    ],
                    'period' => [
                        'start' => '2026-05-29T08:00:00+07:00',
                        'end' => '2026-05-29T10:00:00+07:00' // Tanggal asuhan resmi dinyatakan berakhir/tutup kasus
                    ],
                    'careManager' => [
                        'reference' => 'Practitioner/N10000001',
                        'display' => 'Nama Dokter DPJP Utama'
                    ]
                ]
            ],

            // ==========================================================
            // 4. EPISODE OF CARE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_episode_of_care',
                'label' => 'Patch EpisodeOfCare',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/EpisodeOfCare/{id}',
                'description' => 'Memperbarui status atau elemen data episode asuhan pasien secara parsial (sebagian) menggunakan format array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',           // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',         // Target elemen status asuhan
                        'value' => 'onhold'          // Mengubah status menjadi ditangguhkan sementara (onhold) secara instan
                    ]
                ],
            ],

            // [
            //     'key' => 'delete_episode_of_care',
            //     'label' => 'Delete EpisodeOfCare',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/EpisodeOfCare/{id}',
            //     'description' => 'Delete episode of care',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'EpisodeOfCare ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_episode_of_care',
                'label' => 'History EpisodeOfCare',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/EpisodeOfCare/{id}/_history',
                'description' => 'Get episode of care history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_episode_of_care',
            //     'label' => 'History Type EpisodeOfCare',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/EpisodeOfCare/_history',
            //     'description' => 'Get all episode of care history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // BUNDLE
    // =====================================================

    'bundle' => [
        'label' => 'Bundle',
        'icon' => '📦',
        'description' => 'FHIR transaction bundle',
        'endpoints' => [

            [
                'key' => 'create_bundle',
                'label' => 'Create Transaction Bundle',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Bundle',
                'description' => 'Create FHIR bundle',
                'params' => [],
                'body' => [
                    'resourceType' => 'Bundle',
                    'type' => 'transaction',
                    'entry' => []
                ]
            ],

        ]
    ],

    // =====================================================
    // PROVENANCE
    // =====================================================

    'provenance' => [
        'label' => 'Provenance',
        'icon' => '🕓',
        'description' => 'Audit trail',
        'endpoints' => [

            [
                'key' => 'create_provenance',
                'label' => 'Create Provenance',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Provenance',
                'description' => 'Create provenance',
                'params' => [],
                'body' => [
                    'resourceType' => 'Provenance',

                    'recorded' => date('c'),

                    'target' => [
                        [
                            'reference' => 'Patient/{patient_id}'
                        ]
                    ]
                ]
            ],

            [
                'key' => 'get_provenance',
                'label' => 'Get Provenance by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Provenance/{id}',
                'description' => 'Get provenance by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Provenance ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_provenance',
                'label' => 'Search Provenance by Target',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Provenance?target={target_id}',
                'description' => 'Search provenance by target',
                'params' => [
                    [
                        'name' => 'target_id',
                        'type' => 'text',
                        'placeholder' => 'Target ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'update_provenance',
                'label' => 'Update Provenance',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Provenance/{id}',
                'description' => 'Update provenance',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Provenance ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'delete_provenance',
                'label' => 'Delete Provenance',
                'method' => 'DELETE',
                'path' => '/fhir-r4/v1/Provenance/{id}',
                'description' => 'Delete provenance',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Provenance ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'history_provenance',
                'label' => 'History Provenance',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Provenance/{id}/_history',
                'description' => 'Get provenance history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Provenance ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_provenance',
            //     'label' => 'History Type Provenance',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Provenance/_history',
            //     'description' => 'Get all provenance history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // CARE TEAM
    // =====================================================

    'careteam' => [
        'label' => 'CareTeam',
        'icon' => '👥',
        'description' => 'Care team members and roles',
        'endpoints' => [
            [
                'key' => 'create_careteam',
                'label' => 'Create CareTeam',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/CareTeam',
                'description' => 'Create care team',
                'params' => [],
                'body' => [
                    'resourceType' => 'CareTeam',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
            [
                'key' => 'get_careteam',
                'label' => 'Get CareTeam by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CareTeam?subject={patient_id}',
                'description' => 'Get care teams for patient',
                'params' => [['name' => 'patient_id', 'type' => 'text', 'placeholder' => 'Patient ID', 'default' => '']],
            ],
        ]
    ],

    // =====================================================
    // DETECTED ISSUE
    // =====================================================

    'detectedissue' => [
        'label' => 'DetectedIssue',
        'icon' => '⚠️',
        'description' => 'Detected issues and clinical warnings',
        'endpoints' => [
            [
                'key' => 'create_detectedissue',
                'label' => 'Create DetectedIssue',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/DetectedIssue',
                'description' => 'Create detected issue',
                'params' => [],
                'body' => [
                    'resourceType' => 'DetectedIssue',
                    'status' => 'final',
                    'code' => [
                        'coding' => [
                            ['system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode', 'code' => '', 'display' => '']
                        ]
                    ],
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // CLINICAL IMPRESSION
    // =====================================================

    'clinicalimpression' => [
        'label' => 'ClinicalImpression',
        'icon' => '📋',
        'description' => 'Clinical impressions and assessments',
        'endpoints' => [

            // ==========================================================
            // 2. CLINICAL IMPRESSION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_clinical_impression',
                'label' => 'Create ClinicalImpression',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/ClinicalImpression',
                'description' => 'Mendaftarkan data penilaian atau kesan klinis awal hasil pemeriksaan pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'ClinicalImpression',
                    'status' => 'completed', // Status: in-progress | completed | entered-in-error
                    'description' => 'Berdasarkan hasil anamnesis dan pemeriksaan fisik, pasien menunjukkan gejala klinis gastroenteritis akut dengan dehidrasi ringan.', // Deskripsi naratif ringkasan temuan dokter
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-9617-e98be002cdfa' // ID Encounter/Kunjungan terkait
                    ],
                    'effectiveDateTime' => '2026-05-29T10:00:00+07:00', // Waktu pelaksanaan penilaian klinis dilakukan
                    'date' => '2026-05-29T10:15:00+07:00', // Waktu rekam data ini dibuat di SIMRS
                    'assessor' => [
                        'reference' => 'Practitioner/N10000001' // ID Tenaga Kesehatan / Dokter Pemeriksa yang memberikan penilaian
                    ],
                    'investigation' => [
                        [
                            'code' => [
                                'text' => 'Pemeriksaan Fisik Abdomen' // Kategori/grup investigasi yang mendasari kesan klinis
                            ],
                            'item' => [
                                [
                                    'display' => 'Nyeri tekan pada area epigastrium, bising usus meningkat (18x/menit).' // Detail temuan objektif spesifik
                                ]
                            ]
                        ]
                    ],
                    'summary' => 'Gastroenteritis Akut Dehidrasi Ringan-Sedang', // Kesimpulan akhir atau draf diagnosis kerja
                    'prognosisOutcome' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct', //http://terminology.hl7.org/CodeSystem/clinicalimpression jika pakai hl7
                                    'code' => '170968001', // Contoh kode SNOMED CT untuk prognosis bonam / baik
                                    'display' => 'Prognosis good'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            [
                'key' => 'get_clinicalimpression',
                'label' => 'Get ClinicalImpression by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ClinicalImpression/{id}',
                'description' => 'Get clinical impression by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'ClinicalImpression ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'search_clinicalimpression',
                'label' => 'Search ClinicalImpression by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ClinicalImpression?subject={patient_id}',
                'description' => 'Search clinical impressions for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. CLINICAL IMPRESSION - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_clinical_impression_by_patient_encounter',
                'label' => 'Search ClinicalImpression by Patiend & Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ClinicalImpression?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari data rekaman kesan/penilaian klinis dokter (ClinicalImpression) berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Kunjungan (cth: 4f735a03-128b-464d-9617-e98be002cdfa)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. CLINICAL IMPRESSION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_clinical_impression',
                'label' => 'Update ClinicalImpression',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/ClinicalImpression/{id}',
                'description' => 'Memperbarui rekam data penilaian klinis secara keseluruhan berdasarkan ID ClinicalImpression. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'ClinicalImpression ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'ClinicalImpression',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'completed',
                    'description' => 'Berdasarkan peninjauan ulang dan hasil lab tambahan, dipastikan kondisi mengarah pada infeksi bakteri saluran pencernaan.', // Skenario pembaruan: Perubahan deskripsi klinis lanjutan
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-9617-e98be002cdfa'
                    ],
                    'effectiveDateTime' => '2026-05-29T10:00:00+07:00',
                    'date' => '2026-05-29T10:15:00+07:00',
                    'assessor' => [
                        'reference' => 'Practitioner/N10000001'
                    ],
                    'investigation' => [
                        [
                            'code' => [
                                'text' => 'Pemeriksaan Fisik Abdomen & Hasil Feses Lengkap'
                            ],
                            'item' => [
                                [
                                    'display' => 'Nyeri tekan epigastrium melunak, hasil laboratorium feses menunjukkan leukosit positif.'
                                ]
                            ]
                        ]
                    ],
                    'summary' => 'Gastroenteritis Bakterial', // Pembaruan kesimpulan diagnosis kerja yang lebih spesifik
                    'prognosisOutcome' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://snomed.info/sct',
                                    'code' => '170968001',
                                    'display' => 'Prognosis good'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. CLINICAL IMPRESSION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_clinical_impression',
                'label' => 'Patch ClinicalImpression',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/ClinicalImpression/{id}',
                'description' => 'Memperbarui status atau elemen rekam kesan klinis secara parsial (sebagian) menggunakan format array JSON Patch (misalnya membatalkan rekam data akibat salah input).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'ClinicalImpression ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',           // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',         // Target elemen status
                        'value' => 'entered-in-error' // Mengubah status menjadi entered-in-error secara instan jika ada salah pencatatan oleh dokter
                    ]
                ],
            ],
            
            // [
            //     'key' => 'delete_clinicalimpression',
            //     'label' => 'Delete ClinicalImpression',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/ClinicalImpression/{id}',
            //     'description' => 'Delete clinical impression',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'ClinicalImpression ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            [
                'key' => 'history_clinicalimpression',
                'label' => 'History ClinicalImpression',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ClinicalImpression/{id}/_history',
                'description' => 'Get clinical impression history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'ClinicalImpression ID',
                        'default' => ''
                    ]
                ],
            ],
            // [
            //     'key' => 'history_type_clinicalimpression',
            //     'label' => 'History Type ClinicalImpression',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/ClinicalImpression/_history',
            //     'description' => 'Get all clinical impression history',
            //     'params' => [],
            // ],
        ]
    ],

    // =====================================================
    // CODE SYSTEM
    // =====================================================

    'codesystem' => [
        'label' => 'CodeSystem',
        'icon' => '🔢',
        'description' => 'Code systems and terminologies',
        'endpoints' => [
            [
                'key' => 'get_codesystem',
                'label' => 'Get CodeSystem',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CodeSystem',
                'description' => 'Get code systems',
                'params' => [],
            ],
            [
                'key' => 'get_codesystem_by_id',
                'label' => 'Get CodeSystem by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CodeSystem/{id}',
                'description' => 'Get code system by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CodeSystem ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'search_codesystem',
                'label' => 'Search CodeSystem',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CodeSystem?url={url}',
                'description' => 'Search code systems by URL',
                'params' => [
                    [
                        'name' => 'url',
                        'type' => 'text',
                        'placeholder' => 'URL',
                        'default' => ''
                    ]
                ],
            ],
        ]
    ],

    // =====================================================
    // COMPOSITION
    // =====================================================

    'composition' => [
        'label' => 'Composition',
        'icon' => '📄',
        'description' => 'Clinical documents and summaries',
        'endpoints' => [
            // [
            //     'key' => 'create_composition',
            //     'label' => 'Create Composition',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Composition',
            //     'description' => 'Create composition',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Composition',
            //         'status' => 'final',
            //         'type' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://terminology.hl7.org/CodeSystem/composition-type',
            //                     'code' => 'summary',
            //                     'display' => 'Summary'
            //                 ]
            //             ]
            //         ],
            //         'subject' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],
            // [
            //     'key' => 'get_composition',
            //     'label' => 'Get Composition by ID',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Composition/{id}',
            //     'description' => 'Get composition by ID',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Composition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            // [
            //     'key' => 'search_composition',
            //     'label' => 'Search Composition by Patient',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Composition?subject={patient_id}',
            //     'description' => 'Search compositions for patient',
            //     'params' => [
            //         [
            //             'name' => 'patient_id',
            //             'type' => 'text',
            //             'placeholder' => 'Patient ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            // [
            //     'key' => 'search_composition_by_encounter',
            //     'label' => 'Search Composition by Encounter ID',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Composition?encounter={encounter_id}',
            //     'description' => 'Mencari dokumen ringkasan klinis (Composition / Resume Medis) yang terkait dengan satu kunjungan (Encounter) tertentu.',
            //     'params' => [
            //         [
            //             'name' => 'encounter_id',
            //             'type' => 'text',
            //             'placeholder' => 'Format: UUID (contoh: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            
            // [
            //     'key' => 'update_composition',
            //     'label' => 'Update Composition',
            //     'method' => 'PUT',
            //     'path' => '/fhir-r4/v1/Composition/{id}',
            //     'description' => 'Update composition',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Composition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            // [
            //     'key' => 'delete_composition',
            //     'label' => 'Delete Composition',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/Composition/{id}',
            //     'description' => 'Delete composition',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Composition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            // [
            //     'key' => 'history_composition',
            //     'label' => 'History Composition',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Composition/{id}/_history',
            //     'description' => 'Get composition history',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Composition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'search_composition_by_encounter',
                'label' => 'Search Composition by Encounter ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Composition?encounter={encounter_id}',
                'description' => 'Mencari dokumen ringkasan klinis (Composition / Resume Medis) yang terkait dengan satu kunjungan (Encounter) tertentu.',
                'params' => [
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (contoh: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. COMPOSITION - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_composition',
                'label' => 'Search Composition By Patiend and Encounter Id',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Composition?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari dokumen klinis (Composition) berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Kunjungan (cth: 4f735a03-128b-464d-bf91-e6eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. COMPOSITION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_composition',
                'label' => 'Create Composition',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Composition',
                'description' => 'Mendaftarkan dokumen resume medis/klinis baru (Composition) ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Composition',
                    'status' => 'final', // Pilihan status: preliminary | final | amended | entered-in-error
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '11488-4', // Contoh kode LOINC untuk Consultation Note / Discharge Summary
                                'display' => 'Consultation note'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // Ganti dengan ID Pasien asli
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f' // Mengikat ID Encounter kunjungan
                    ],
                    'date' => '2026-05-29T16:00:00+07:00', // Waktu pembuatan dokumen
                    'author' => [
                        [
                            'reference' => 'Practitioner/N10000001', // ID Dokter pembuat dokumen
                            'display' => 'Nama Dokter Penanggung Jawab'
                        ]
                    ],
                    'title' => 'Resume Medis Pasien',
                    'custodian' => [
                        'reference' => 'Organization/100028344' // ID Fasyankes / Rumah Sakit utama
                    ],
                    'section' => [
                        [
                            'title' => 'Adverse Reactions',
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://loinc.org',
                                        'code' => '48765-2',
                                        'display' => 'Allergies and adverse reactions Document'
                                    ]
                                ]
                            ],
                            'text' => [
                                'status' => 'generated',
                                'div' => '<div xmlns="http://www.w3.org/1999/xhtml">Tidak ada riwayat alergi obat.</div>'
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. COMPOSITION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_composition',
                'label' => 'Update Composition',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Composition/{id}',
                'description' => 'Memperbarui isi dokumen klinis secara keseluruhan berdasarkan ID Composition. ID di dalam body harus sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Composition ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Composition',
                    'id' => '{id}', // WAJIB ada dan bernilai sama dengan parameter {id} di path URL
                    'status' => 'final',
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => '11488-4',
                                'display' => 'Consultation note'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP (Updated)'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e6eacdf1c38f'
                    ],
                    'date' => '2026-05-29T16:15:00+07:00', // Jam modifikasi dokumen
                    'author' => [
                        [
                            'reference' => 'Practitioner/N10000001',
                            'display' => 'Nama Dokter Penanggung Jawab'
                        ]
                    ],
                    'title' => 'Resume Medis Pasien (Terubah)',
                    'custodian' => [
                        'reference' => 'Organization/100028344'
                    ],
                    'section' => [
                        [
                            'title' => 'Adverse Reactions',
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://loinc.org',
                                        'code' => '48765-2',
                                        'display' => 'Allergies and adverse reactions Document'
                                    ]
                                ]
                            ],
                            'text' => [
                                'status' => 'generated',
                                'div' => '<div xmlns="http://www.w3.org/1999/xhtml">Ada riwayat alergi ringan terhadap Amoxicillin.</div>'
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. COMPOSITION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_composition',
                'label' => 'Patch Composition',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Composition/{id}',
                'description' => 'Memperbarui elemen dokumen klinis secara parsial (sebagian) menggunakan format array JSON Patch (misal: merevisi judul atau mengubah status dokumen saja).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Composition ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/title',      // Target elemen judul dokumen
                        'value' => 'Resume Medis Pasien Akhir (Revisi Otomatis)' // Nilai baru
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/status',     // Mengubah status dokumen dari draft / preliminary menjadi final
                        'value' => 'final'
                    ]
                ],
            ],
            
        ]
    ],

    // =====================================================
    // SUBSTANCE NUCLEIC ACID
    // =====================================================

    'substancenucleicacid' => [
        'label' => 'SubstanceNucleicAcid',
        'icon' => '🧬',
        'description' => 'Substance nucleic acids',
        'endpoints' => [
            [
                'key' => 'get_subnucleic',
                'label' => 'Get SubstanceNucleicAcid',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceNucleicAcid',
                'description' => 'Get substance nucleic acids',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // ADVERSE EVENT
    // =====================================================

    'adverseevent' => [
        'label' => 'AdverseEvent',
        'icon' => '⚠️',
        'description' => 'Adverse events and safety reports',
        'endpoints' => [
            [
                'key' => 'create_adverseevent',
                'label' => 'Create AdverseEvent',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/AdverseEvent',
                'description' => 'Create adverse event',
                'params' => [],
                'body' => [
                    'resourceType' => 'AdverseEvent',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // ALLERGY INTOLERANCE
    // =====================================================

    'allergyintolerance' => [
        'label' => 'AllergyIntolerance',
        'icon' => '⚠️',
        'description' => 'Allergy resources',
        'endpoints' => [

            // [
            //     'key' => 'create_allergyintolerance',
            //     'label' => 'Create AllergyIntolerance',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/AllergyIntolerance',
            //     'description' => 'Create allergy',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'AllergyIntolerance',

            //         'clinicalStatus' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
            //                     'code' => 'active'
            //                 ]
            //             ]
            //         ],

            //         'verificationStatus' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
            //                     'code' => 'confirmed'
            //                 ]
            //             ]
            //         ],

            //         'code' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://snomed.info/sct',
            //                     'code' => '',
            //                     'display' => ''
            //                 ]
            //             ]
            //         ],

            //         'patient' => [
            //             'reference' => 'Patient/{patient_id}'
            //         ]
            //     ]
            // ],

            // ==========================================================
            // 1. ALLERGY INTOLERANCE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_allergy_intolerance',
                'label' => 'Search AllergyIntolerance',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/AllergyIntolerance?patient={patient_id}',
                'description' => 'Mencari rekam medis riwayat alergi pasien (AllergyIntolerance) berdasarkan ID Pasien (patient).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. ALLERGY INTOLERANCE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_allergy_intolerance',
                'label' => 'Create AllergyIntolerance',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/AllergyIntolerance',
                'description' => 'Mendaftarkan data riwayat alergi atau intoleransi baru pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'AllergyIntolerance',
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
                                'code' => 'active', // Status klinis: active | inactive | resolved
                                'display' => 'Active'
                            ]
                        ]
                    ],
                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
                                'code' => 'confirmed', // Status verifikasi: unconfirmed | confirmed | refuted | entered-in-error
                                'display' => 'Confirmed'
                            ]
                        ]
                    ],
                    'category' => [
                        'medication' // Kategori alergi: medication | food | biologic | environment | biocompatibility
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '764146007', // Contoh kode SNOMED CT untuk Alergi Obat Amoxicillin
                                'display' => 'Allergy to amoxicillin'
                            ]
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001', // Ganti dengan ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29' // Mengikat ID Encounter kunjungan pasien
                    ],
                    'recordedDate' => '2026-05-29T10:00:00+07:00', // Waktu riwayat alergi ini dicatat
                    'recorder' => [
                        'reference' => 'Practitioner/N10000001' // ID Tenaga Kesehatan / Dokter yang mencatat riwayat alergi
                    ]
                ]
            ],

            // ==========================================================
            // 3. ALLERGY INTOLERANCE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_allergy_intolerance',
                'label' => 'Update AllergyIntolerance',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/AllergyIntolerance/{id}',
                'description' => 'Memperbarui data riwayat alergi secara keseluruhan berdasarkan ID AllergyIntolerance. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'AllergyIntolerance ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'AllergyIntolerance',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
                                'code' => 'resolved', // Skenario pembaruan: diubah menjadi resolved karena alergi pasien dinyatakan sudah sembuh/hilang
                                'display' => 'Resolved'
                            ]
                        ]
                    ],
                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
                                'code' => 'confirmed',
                                'display' => 'Confirmed'
                            ]
                        ]
                    ],
                    'category' => [
                        'medication'
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '764146007',
                                'display' => 'Allergy to amoxicillin'
                            ]
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29'
                    ],
                    'recordedDate' => '2026-05-29T10:00:00+07:00',
                    'recorder' => [
                        'reference' => 'Practitioner/N10000001'
                    ]
                ]
            ],

            // ==========================================================
            // 4. ALLERGY INTOLERANCE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_allergy_intolerance',
                'label' => 'Patch AllergyIntolerance',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/AllergyIntolerance/{id}',
                'description' => 'Memperbarui data elemen riwayat alergi secara parsial (sebagian) menggunakan format array JSON Patch (sangat berguna untuk merubah status klinis secara cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'AllergyIntolerance ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',                                         // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/clinicalStatus/coding/0/code',                 // Target elemen kode status klinis
                        'value' => 'inactive'                                      // Nilai baru, diubah menjadi tidak aktif
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/clinicalStatus/coding/0/display',              // Target teks tampilan status klinis
                        'value' => 'Inactive'
                    ]
                ],
            ],

        ]
    ],

    // =====================================================
    // CONDITION
    // =====================================================

    'condition' => [
        'label' => 'Condition',
        'icon' => '🤒',
        'description' => 'Condition and problem list',
        'endpoints' => [
            // ==========================================================
            // 2. CONDITION - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_condition',
                'label' => 'Create Condition',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Condition',
                'description' => 'Mencatatkan data diagnosis penyakit atau keluhan klinis baru pasien ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Condition',
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                                'code' => 'active', // active | recurrence | relapse | inactive | remission | resolved
                                'display' => 'Active'
                            ]
                        ]
                    ],
                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                                'code' => 'confirmed', // unconfirmed | provisional | differential | confirmed | refuted | entered-in-error
                                'display' => 'Confirmed'
                            ]
                        ]
                    ],
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                                    'code' => 'encounter-diagnosis', // encounter-diagnosis = Diagnosis Kunjungan, problem-list-item = Riwayat Masalah
                                    'display' => 'Encounter Diagnosis'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-10',
                                'code' => 'K35.8', // Kode ICD-10 (contoh: Acute appendicitis, other and unspecified)
                                'display' => 'Acute appendicitis, other and unspecified'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e7eacdf1c38f', // Mengikat ID Encounter kunjungan terkait
                        'display' => 'Kunjungan Terkait Diagnosis Ini'
                    ],
                    'onsetDateTime' => '2026-05-29T08:00:00+07:00', // Waktu pertama kali gejala/kondisi dirasakan
                    'recordedDate' => '2026-05-29T10:15:00+07:00' // Waktu saat diagnosis dicatat di sistem
                ]
            ],
            [
                'key' => 'get_condition_id',
                'label' => 'Get Condition by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Condition/{id}',
                'description' => 'Mengambil data kondisi penyakit berdasarkan ID.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Condition SATUSEHAT', 'default' => '']
                ]
            ],
            // [
            //     'key' => 'create_condition',
            //     'label' => 'Create Condition',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/Condition',
            //     'description' => 'Create condition',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Condition',
            //         'clinicalStatus' => 'active',
            //         'verificationStatus' => 'confirmed',
            //         'category' => [
            //             [
            //                 'coding' => [
            //                     [
            //                         'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
            //                         'code' => 'problem-list-item'
            //                     ]
            //                 ]
            //             ]
            //         ],
            //         'code' => [
            //             'coding' => [
            //                 [
            //                     'system' => 'http://snomed.info/sct',
            //                     'code' => '',
            //                     'display' => ''
            //                 ]
            //             ]
            //         ],
            //         'subject' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],
            // [
            //     'key' => 'get_condition',
            //     'label' => 'Get Condition by ID',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Condition/{id}',
            //     'description' => 'Get condition by ID',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Condition ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            [
                'key' => 'search_condition',
                'label' => 'Search Condition by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Condition?subject={patient_id}',
                'description' => 'Search conditions for patient',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 1. CONDITION - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_condition_by_patiend_id_and_encounter_id',
                'label' => 'Search Condition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Condition?subject={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari data diagnosis atau kondisi klinis pasien berdasarkan ID Pasien (subject) dan/atau ID Kunjungan (encounter).',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Kunjungan (cth: 4f735a03-128b-464d-bf91-e7eacdf1c38f)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 3. CONDITION - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_condition',
                'label' => 'Update Condition',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Condition/{id}',
                'description' => 'Memperbarui dokumen diagnosis secara keseluruhan berdasarkan ID Condition. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Condition ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Condition',
                    'id' => '{id}', // WAJIB disertakan pada metode PUT dan harus sama dengan parameter URL
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                                'code' => 'resolved', // Skenario pembaruan: Kondisi penyakit diubah dari 'active' menjadi 'resolved' (sembuh)
                                'display' => 'Resolved'
                            ]
                        ]
                    ],
                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                                'code' => 'confirmed',
                                'display' => 'Confirmed'
                            ]
                        ]
                    ],
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                                    'code' => 'encounter-diagnosis',
                                    'display' => 'Encounter Diagnosis'
                                ]
                            ]
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-10',
                                'code' => 'K35.8',
                                'display' => 'Acute appendicitis, other and unspecified'
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/4f735a03-128b-464d-bf91-e7eacdf1c38f'
                    ],
                    'onsetDateTime' => '2026-05-29T08:00:00+07:00',
                    'recordedDate' => '2026-05-29T10:15:00+07:00',
                    'abatementDateTime' => '2026-05-29T18:00:00+07:00' // Ditambahkan waktu kesembuhan/redanya penyakit
                ]
            ],

            // ==========================================================
            // 4. CONDITION - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_condition',
                'label' => 'Patch Condition',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Condition/{id}',
                'description' => 'Memperbarui elemen informasi rekam diagnosis secara parsial (sebagian) menggunakan spesifikasi operasi array JSON Patch.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Condition ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',
                        'path' => '/clinicalStatus/coding/0/code', // Mengubah nilai kode status klinis secara spesifik
                        'value' => 'inactive'
                    ]
                ],
            ],
        ]
    ],

    // =====================================================
    // APPOINTMENT RESPONSE
    // =====================================================

    'appointmentresponse' => [
        'label' => 'AppointmentResponse',
        'icon' => '✅',
        'description' => 'Appointment responses and confirmations',
        'endpoints' => [
            // [
            //     'key' => 'create_appointmentresponse',
            //     'label' => 'Create AppointmentResponse',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/AppointmentResponse',
            //     'description' => 'Create appointment response',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'AppointmentResponse',
            //         'status' => 'accepted',
            //         'appointment' => ['reference' => 'Appointment/{appointment_id}']
            //     ]
            // ],
            
            // ==========================================================
            // 1. APPOINTMENT RESPONSE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_appointment_response',
                'label' => 'Search AppointmentResponse',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/AppointmentResponse?appointment={appointment_id}',
                'description' => 'Mencari data status konfirmasi kehadiran/tanggapan (AppointmentResponse) berdasarkan ID Appointment (Janji Temu) terkait.',
                'params' => [
                    [
                        'name' => 'appointment_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Janji Temu (cth: 98eaf00e-4464-4dfc-a3cc-da9646f99108)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. APPOINTMENT RESPONSE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_appointment_response',
                'label' => 'Create AppointmentResponse',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/AppointmentResponse',
                'description' => 'Mendaftarkan konfirmasi persetujuan kehadiran baru dari dokter/pasien terhadap jadwal janji temu ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'AppointmentResponse',
                    'appointment' => [
                        'reference' => 'Appointment/98eaf00e-4464-4dfc-a3cc-da9646f99108', // Merujuk pada ID Appointment induk
                        'display' => 'Pemeriksaan Rutin Poli Anak'
                    ],
                    'actor' => [
                        'reference' => 'Practitioner/N10000001', // ID Partisipan yang merespons (bisa Practitioner atau Patient)
                        'display' => 'Nama Dokter Spesialis Anak'
                    ],
                    'participantStatus' => 'accepted', // Status konfirmasi: accepted | declined | tentative | needs-action
                    'comment' => 'Konfirmasi bersedia hadir melayani sesuai jam janji temu.'
                ]
            ],

            // ==========================================================
            // 3. APPOINTMENT RESPONSE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_appointment_response',
                'label' => 'Update AppointmentResponse',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/AppointmentResponse/{id}',
                'description' => 'Memperbarui data konfirmasi janji temu secara keseluruhan berdasarkan ID AppointmentResponse. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'AppointmentResponse ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'AppointmentResponse',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'appointment' => [
                        'reference' => 'Appointment/98eaf00e-4464-4dfc-a3cc-da9646f99108',
                        'display' => 'Pemeriksaan Rutin Poli Anak'
                    ],
                    'actor' => [
                        'reference' => 'Practitioner/N10000001',
                        'display' => 'Nama Dokter Spesialis Anak'
                    ],
                    'participantStatus' => 'declined', // Contoh perubahan respon: diubah dari accepted menjadi declined karena dokter mendadak berhalangan
                    'comment' => 'Pembatalan kehadiran karena ada keperluan tindakan operasi darurat.'
                ]
            ],

            // ==========================================================
            // 4. APPOINTMENT RESPONSE - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_appointment_response',
                'label' => 'Patch AppointmentResponse',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/AppointmentResponse/{id}',
                'description' => 'Memperbarui status tanggapan partisipan secara parsial (sebagian) menggunakan format array JSON Patch (sangat ideal untuk mengubah status response secara instan).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'AppointmentResponse ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',               // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/participantStatus', // Target elemen status tanggapan
                        'value' => 'accepted'           // Mengubah nilai kembali menjadi accepted secara cepat
                    ]
                ],
            ],
        ]
    ],

    // =====================================================
    // CONTRACT
    // =====================================================

    'contract' => [
        'label' => 'Contract',
        'icon' => '📝',
        'description' => 'Contracts and agreements',
        'endpoints' => [
            [
                'key' => 'create_contract',
                'label' => 'Create Contract',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Contract',
                'description' => 'Create contract',
                'params' => [],
                'body' => [
                    'resourceType' => 'Contract',
                    'status' => 'active'
                ]
            ],
        ]
    ],

    // =====================================================
    // SCHEDULE
    // =====================================================

    'schedule' => [
        'label' => 'Schedule',
        'icon' => '📅',
        'description' => 'Schedules and availability',
        'endpoints' => [
            [
                'key' => 'get_schedule',
                'label' => 'Get Schedule',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Schedule',
                'description' => 'Get schedules',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SLOT
    // =====================================================

    'slot' => [
        'label' => 'Slot',
        'icon' => '⏰',
        'description' => 'Time slots for appointments',
        'endpoints' => [
            [
                'key' => 'get_slot',
                'label' => 'Get Slot',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Slot',
                'description' => 'Get slots',
                'params' => [],
            ],
            // ==========================================================
            // 1. SLOT - GET DETAIL DATA BY ID
            // ==========================================================
            [
                'key' => 'get_slot_detail',
                'label' => 'Get Slot Detail',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Slot/{id}',
                'description' => 'Mendapatkan informasi detail ketersediaan celah waktu (Slot) janji temu berdasarkan ID Slot unik di SATUSEHAT.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Slot ID (Format: UUID, cth: e2fdfc6f-28ff-46be-b68b-b73d982cdcf8)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. SLOT - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_slot',
                'label' => 'Create Slot',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Slot',
                'description' => 'Mendaftarkan celah jadwal waktu (Slot) baru untuk pelayanan dokter atau poliklinik ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Slot',
                    'appointmentType' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v4-0276',
                                'code' => 'ROUTINE', // ROUTINE = Kunjungan Rutin/Biasa, WALKIn = Pasien Datang Langsung tanpa Janji
                                'display' => 'Routine visit'
                            ]
                        ]
                    ],
                    'schedule' => [
                        'reference' => 'Schedule/67890-schedule-id' // Merujuk pada ID Schedule (Induk Jadwal) Dokter terkait
                    ],
                    'status' => 'free', // Status ketersediaan: free (kosong) | busy (terisi) | busy-tentative | onhold | entered-in-error
                    'start' => '2026-06-01T09:00:00+07:00', // Waktu mulai slot janji temu
                    'end' => '2026-06-01T09:15:00+07:00',   // Waktu selesai slot janji temu
                    'comment' => 'Slot Pemeriksaan Rutin Poli Anak Kulit'
                ]
            ],

            // ==========================================================
            // 3. SLOT - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_slot',
                'label' => 'Update Slot',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Slot/{id}',
                'description' => 'Memperbarui data celah jadwal janji temu secara keseluruhan berdasarkan ID Slot. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Slot ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Slot',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'appointmentType' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v4-0276',
                                'code' => 'ROUTINE',
                                'display' => 'Routine visit'
                            ]
                        ]
                    ],
                    'schedule' => [
                        'reference' => 'Schedule/67890-schedule-id'
                    ],
                    'status' => 'busy', // Contoh perubahan status: diubah menjadi busy karena celah waktu sudah di-booking pasien
                    'start' => '2026-06-01T09:00:00+07:00',
                    'end' => '2026-06-01T09:15:00+07:00',
                    'comment' => 'Slot Pemeriksaan Rutin Poli Anak Kulit (Sudah Terisi)'
                ]
            ],

            // ==========================================================
            // 4. SLOT - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_slot',
                'label' => 'Patch Slot',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/Slot/{id}',
                'description' => 'Memperbarui data elemen celah jadwal secara parsial (sebagian) menggunakan format array JSON Patch (sangat berguna untuk merubah status free/busy celah waktu janji temu secara instan).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Slot ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status celah jadwal
                        'value' => 'busy'        // Mengubah status menjadi terisi/ter-booking secara cepat
                    ]
                ],
            ],
        ]
    ],

    // =====================================================
    // STRUCTURE DEFINITION
    // =====================================================

    'structuredefinition' => [
        'label' => 'StructureDefinition',
        'icon' => '🏗️',
        'description' => 'Structure definitions',
        'endpoints' => [
            [
                'key' => 'get_structuredef',
                'label' => 'Get StructureDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/StructureDefinition',
                'description' => 'Get structure definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // VALUE SET
    // =====================================================

    'valueset' => [
        'label' => 'ValueSet',
        'icon' => '📋',
        'description' => 'Value sets and codings',
        'endpoints' => [
            [
                'key' => 'get_valueset',
                'label' => 'Get ValueSet',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ValueSet',
                'description' => 'Get value sets',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // QUESTIONNAIRE
    // =====================================================

    'questionnaire' => [
        'label' => 'Questionnaire',
        'icon' => '❓',
        'description' => 'Questionnaires and surveys',
        'endpoints' => [
            [
                'key' => 'create_questionnaire',
                'label' => 'Create Questionnaire',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Questionnaire',
                'description' => 'Create questionnaire',
                'params' => [],
                'body' => [
                    'resourceType' => 'Questionnaire',
                    'status' => 'active',
                    'title' => '',
                    'subjectType' => ['Patient']
                ]
            ],
        ]
    ],

    // =====================================================
    // QUESTIONNAIRE RESPONSE
    // =====================================================

    'questionnaireresponse' => [
        'label' => 'QuestionnaireResponse',
        'icon' => '❓',
        'description' => 'Questionnaire responses',
        'endpoints' => [
            // [
            //     'key' => 'create_questionnaireresponse',
            //     'label' => 'Create QuestionnaireResponse',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/QuestionnaireResponse',
            //     'description' => 'Create questionnaire response',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'QuestionnaireResponse',
            //         'status' => 'completed',
            //         'questionnaire' => ['reference' => 'Questionnaire/{questionnaire_id}'],
            //         'subject' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],

            // ==========================================================
            // 1. QUESTIONNAIRE RESPONSE - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_questionnaire_response',
                'label' => 'Search QuestionnaireResponse',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/QuestionnaireResponse?patient={patient_id}&encounter={encounter_id}',
                'description' => 'Mencari dokumen hasil kuesioner/asesmen (QuestionnaireResponse) berdasarkan ID Pasien (patient) dan ID Kunjungan (encounter). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'encounter_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID Kunjungan (cth: bc5edf78-ea8d-4827-97b3-3c73a810fa29)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. QUESTIONNAIRE RESPONSE - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_questionnaire_response',
                'label' => 'Create QuestionnaireResponse',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/QuestionnaireResponse',
                'description' => 'Mendaftarkan hasil jawaban formulir kuesioner/skrining baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'QuestionnaireResponse',
                    'questionnaire' => 'https://fhir.kemkes.go.id/Questionnaire/Q0001', // Tautan kuesioner referensi standar Kemenkes
                    'status' => 'completed', // Status dokumen: in-progress | completed | amended | entered-in-error
                    'subject' => [
                        'reference' => 'Patient/100000000001', // ID Pasien SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29' // Mengikat ID Encounter kunjungan
                    ],
                    'authored' => '2026-05-29T17:00:00+07:00', // Waktu pengisian kuesioner dilakukan
                    'author' => [
                        'reference' => 'Practitioner/N10000001' // ID Tenaga Kesehatan / Dokter yang mengonfirmasi asesmen
                    ],
                    'item' => [
                        [
                            'linkId' => '1',
                            'text' => 'Apakah pasien mengalami batuk lebih dari 2 minggu?',
                            'answer' => [
                                [
                                    'valueBoolean' => true // Contoh jawaban berupa boolean
                                ]
                            ]
                        ],
                        [
                            'linkId' => '2',
                            'text' => 'Berapa berat badan pasien saat ini? (kg)',
                            'answer' => [
                                [
                                    'valueDecimal' => 65.5 // Contoh jawaban berupa angka desimal
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. QUESTIONNAIRE RESPONSE - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_questionnaire_response',
                'label' => 'Update QuestionnaireResponse',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/QuestionnaireResponse/{id}',
                'description' => 'Memperbarui data jawaban kuesioner secara keseluruhan berdasarkan ID QuestionnaireResponse. ID di dalam body harus disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'QuestionnaireResponse ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'QuestionnaireResponse',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'questionnaire' => 'https://fhir.kemkes.go.id/Questionnaire/Q0001',
                    'status' => 'completed',
                    'subject' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/bc5edf78-ea8d-4827-97b3-3c73a810fa29'
                    ],
                    'authored' => '2026-05-29T17:15:00+07:00', // Waktu modifikasi data jawaban
                    'author' => [
                        'reference' => 'Practitioner/N10000001'
                    ],
                    'item' => [
                        [
                            'linkId' => '1',
                            'text' => 'Apakah pasien mengalami batuk lebih dari 2 minggu?',
                            'answer' => [
                                [
                                    'valueBoolean' => false // Contoh pembaruan/koreksi jawaban dari true ke false
                                ]
                            ]
                        ],
                        [
                            'linkId' => '2',
                            'text' => 'Berapa berat badan pasien saat ini? (kg)',
                            'answer' => [
                                [
                                    'valueDecimal' => 65.5
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ]
    ],

    // =====================================================
    // RISK ASSESSMENT
    // =====================================================

    'riskassessment' => [
        'label' => 'RiskAssessment',
        'icon' => '⚠️',
        'description' => 'Risk assessments and predictions',
        'endpoints' => [
            [
                'key' => 'create_riskassessment',
                'label' => 'Create RiskAssessment',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/RiskAssessment',
                'description' => 'Create risk assessment',
                'params' => [],
                'body' => [
                    'resourceType' => 'RiskAssessment',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // FAMILY MEMBER HISTORY
    // =====================================================

    'familymemberhistory' => [
        'label' => 'FamilyMemberHistory',
        'icon' => '👨‍👩‍👧',
        'description' => 'Family member history and genetic information',
        'endpoints' => [
            // [
            //     'key' => 'create_familymemberhistory',
            //     'label' => 'Create FamilyMemberHistory',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/FamilyMemberHistory',
            //     'description' => 'Create family member history',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'FamilyMemberHistory',
            //         'status' => 'completed',
            //         'patient' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],
            // ==========================================================
            // 1. FAMILY MEMBER HISTORY - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_family_member_history',
                'label' => 'Search FamilyMemberHistory',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/FamilyMemberHistory?patient={patient_id}&relationship={relationship_code}',
                'description' => 'Mencari data riwayat kesehatan keluarga berdasarkan ID Pasien (patient) dan jenis hubungan kekeluargaan (relationship). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'relationship_code',
                        'type' => 'text',
                        'placeholder' => 'Kode Relasi (cth: FTH untuk Ayah, MTH untuk Ibu)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. FAMILY MEMBER HISTORY - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_family_member_history',
                'label' => 'Create FamilyMemberHistory',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/FamilyMemberHistory',
                'description' => 'Mendaftarkan data riwayat penyakit anggota keluarga baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'FamilyMemberHistory',
                    'status' => 'completed', // Pilihan status: partial | completed | entered-in-error | health-unknown
                    'patient' => [
                        'reference' => 'Patient/100000000001', // Ganti dengan ID Pasien Utama SATUSEHAT
                        'display' => 'Nama Pasien Utama'
                    ],
                    'relationship' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                                'code' => 'FTH', // FTH = Father (Ayah), MTH = Mother (Ibu)
                                'display' => 'father'
                            ]
                        ]
                    ],
                    'condition' => [
                        [
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://hl7.org/fhir/sid/icd-10',
                                        'code' => 'E11.9', // Contoh kode ICD-10 untuk Diabetes Melitus Tipe 2 tanpa komplikasi
                                        'display' => 'Type 2 diabetes mellitus without complications'
                                    ]
                                ]
                            ],
                            'outcome' => [
                                'coding' => [
                                    [
                                        'system' => 'http://snomed.info/sct',
                                        'code' => '419099009',
                                        'display' => 'Died' // Status kondisi kerabat (bisa diisi 'Died' jika sudah meninggal)
                                    ]
                                ]
                            ],
                            'note' => [
                                [
                                    'text' => 'Riwayat menderita diabetes melitus tipe 2 sejak usia 45 tahun.'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. FAMILY MEMBER HISTORY - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_family_member_history',
                'label' => 'Update FamilyMemberHistory',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/FamilyMemberHistory/{id}',
                'description' => 'Memperbarui data riwayat kesehatan keluarga secara keseluruhan berdasarkan ID FamilyMemberHistory. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'FamilyMemberHistory ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'FamilyMemberHistory',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'status' => 'completed',
                    'patient' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Utama'
                    ],
                    'relationship' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                                'code' => 'FTH',
                                'display' => 'father'
                            ]
                        ]
                    ],
                    'condition' => [
                        [
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://hl7.org/fhir/sid/icd-10',
                                        'code' => 'E11.9',
                                        'display' => 'Type 2 diabetes mellitus without complications (Updated)'
                                    ]
                                ]
                            ],
                            'outcome' => [
                                'coding' => [
                                    [
                                        'system' => 'http://snomed.info/sct',
                                        'code' => '419099009',
                                        'display' => 'Died'
                                    ]
                                ]
                            ],
                            'note' => [
                                [
                                    'text' => 'Riwayat menderita diabetes melitus tipe 2 sejak usia 45 tahun (Data telah dikonfirmasi ulang).'
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. FAMILY MEMBER HISTORY - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_family_member_history',
                'label' => 'Patch FamilyMemberHistory',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/FamilyMemberHistory/{id}',
                'description' => 'Memperbarui data riwayat keluarga pasien secara parsial (sebagian) menggunakan format array JSON Patch (misalnya merubah status/kondisi secara cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'FamilyMemberHistory ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/status',     // Target elemen status
                        'value' => 'completed'   // Nilai pengganti status baru
                    ]
                ],
            ],
        ]
    ],

    // =====================================================
    // GOAL
    // =====================================================

    'goal' => [
        'label' => 'Goal',
        'icon' => '🎯',
        'description' => 'Health goals and objectives',
        'endpoints' => [
            [
                'key' => 'create_goal',
                'label' => 'Create Goal',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Goal',
                'description' => 'Create health goal',
                'params' => [],
                'body' => [
                    'resourceType' => 'Goal',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // MEDICATION ADMINISTRATION
    // =====================================================

    'medicationadministration' => [
        'label' => 'MedicationAdministration',
        'icon' => '💉',
        'description' => 'Medication administration records',
        'endpoints' => [
            [
                'key' => 'get_medicationadministration',
                'label' => 'Get MedicationAdministration by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationAdministration?subject={patient_id}',
                'description' => 'Get medication administrations for patient',
                'params' => [['name' => 'patient_id', 'type' => 'text', 'placeholder' => 'Patient ID', 'default' => '']],
            ],
        ]
    ],

    // =====================================================
    // MEDICATION KNOWLEDGE
    // =====================================================

    'medicationknowledge' => [
        'label' => 'MedicationKnowledge',
        'icon' => '📚',
        'description' => 'Medication knowledge and information',
        'endpoints' => [
            [
                'key' => 'get_medicationknowledge',
                'label' => 'Get MedicationKnowledge by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationKnowledge/{id}',
                'description' => 'Get medication knowledge by ID',
                'params' => [['name' => 'id', 'type' => 'text', 'placeholder' => 'MedicationKnowledge ID', 'default' => '']],
            ],
        ]
    ],

    // =====================================================
    // MEDIA
    // =====================================================

    'media' => [
        'label' => 'Media',
        'icon' => '🖼️',
        'description' => 'Media and imaging attachments',
        'endpoints' => [
            [
                'key' => 'create_media',
                'label' => 'Create Media',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Media',
                'description' => 'Create media record',
                'params' => [],
                'body' => [
                    'resourceType' => 'Media',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // HEALTHCARE SERVICE
    // =====================================================

    'healthcareservice' => [
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
    ],

    // =====================================================
    // ENDPOINT
    // =====================================================

    'endpoint' => [
        'label' => 'Endpoint',
        'icon' => '🔗',
        'description' => 'API endpoints and connections',
        'endpoints' => [
            [
                'key' => 'get_endpoint',
                'label' => 'Get Endpoint',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Endpoint',
                'description' => 'Get endpoints',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // PRACTITIONER ROLE
    // =====================================================

    'practitionerrole' => [
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
    ],

    // =====================================================
    // RELATED PERSON
    // =====================================================

    'relatedperson' => [
        'label' => 'RelatedPerson',
        'icon' => '👨‍👩‍👧',
        'description' => 'Related persons and caregivers',
        'endpoints' => [
            // [
            //     'key' => 'create_relatedperson',
            //     'label' => 'Create RelatedPerson',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/RelatedPerson',
            //     'description' => 'Create related person',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'RelatedPerson',
            //         'patient' => ['reference' => 'Patient/{patient_id}']
            //     ]
            // ],

            // ==========================================================
            // 1. RELATED PERSON - PENCARIAN DATA (GET)
            // ==========================================================
            [
                'key' => 'search_related_person',
                'label' => 'Search Related Person',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/RelatedPerson?identifier=https://fhir.kemkes.go.id/id/nik|{nik}',
                'description' => 'Mencari data relasi/kerabat pasien berdasarkan NIK dari Related Person terkait.',
                'params' => [
                    [
                        'name' => 'nik',
                        'type' => 'text',
                        'placeholder' => '16 Digit NIK Kerabat/Wali Pasien (cth: 317301XXXXXXXXXX)',
                        'default' => ''
                    ]
                ],
            ],

            // ==========================================================
            // 2. RELATED PERSON - PENAMBAHAN DATA (POST)
            // ==========================================================
            [
                'key' => 'create_related_person',
                'label' => 'Create Related Person',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/RelatedPerson',
                'description' => 'Mendaftarkan data wali/kerabat/penanggung jawab pasien baru ke dalam ekosistem SATUSEHAT.',
                'params' => [],
                'body' => [
                    'resourceType' => 'RelatedPerson',
                    'meta' => [
                        'profile' => [
                            'https://fhir.kemkes.go.id/r4/StructureDefinition/RelatedPerson'
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001', // Ganti dengan ID Pasien Utama SATUSEHAT
                        'display' => 'Nama Pasien Utama'
                    ],
                    'relationship' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                                    'code' => 'MTH', // MTH = Mother (Ibu), FTH = Father (Ayah), SPS = Spouse (Pasangan)
                                    'display' => 'mother'
                                ]
                            ]
                        ]
                    ],
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik', // System pemetaan NIK Nasional
                            'value' => '317301XXXXXXXXXX' // Ganti dengan NIK Wali/Kerabat asli
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Nama Lengkap Wali Sesuai KTP'
                        ]
                    ],
                    'gender' => 'female', // Pilihan: male | female | other | unknown
                    'birthDate' => '1975-12-25', // Format: YYYY-MM-DD
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '0812XXXXXXXX',
                            'use' => 'mobile'
                        ]
                    ],
                    'address' => [
                        [
                            'use' => 'home',
                            'line' => [
                                'Alamat Lengkap Rumah Wali Sesuai KTP'
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 3. RELATED PERSON - PEMBARUAN DATA TOTAL (PUT)
            // ==========================================================
            [
                'key' => 'update_related_person',
                'label' => 'Update Related Person',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/RelatedPerson/{id}',
                'description' => 'Memperbarui data wali/kerabat secara keseluruhan berdasarkan ID RelatedPerson. ID di dalam body wajib disertakan dan bernilai sama dengan ID di URL.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'RelatedPerson ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'RelatedPerson',
                    'id' => '{id}', // WAJIB ada di dalam body PUT dan nilainya harus sama dengan {id} di URL
                    'meta' => [
                        'profile' => [
                            'https://fhir.kemkes.go.id/r4/StructureDefinition/RelatedPerson'
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/100000000001',
                        'display' => 'Nama Pasien Utama'
                    ],
                    'relationship' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-RoleCode',
                                    'code' => 'MTH',
                                    'display' => 'mother'
                                ]
                            ]
                        ]
                    ],
                    'identifier' => [
                        [
                            'use' => 'official',
                            'system' => 'https://fhir.kemkes.go.id/id/nik',
                            'value' => '317301XXXXXXXXXX'
                        ]
                    ],
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Nama Lengkap Wali Setelah Diperbarui' // Contoh pembaruan nama
                        ]
                    ],
                    'gender' => 'female',
                    'birthDate' => '1975-12-25',
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '0813XXXXXXXX', // Contoh pembaruan nomor kontak telepon wali
                            'use' => 'mobile'
                        ]
                    ],
                    'address' => [
                        [
                            'use' => 'home',
                            'line' => [
                                'Alamat Lengkap Rumah Wali Sesuai KTP'
                            ]
                        ]
                    ]
                ]
            ],

            // ==========================================================
            // 4. RELATED PERSON - PEMBARUAN SEBAGIAN (PATCH)
            // ==========================================================
            [
                'key' => 'patch_related_person',
                'label' => 'Patch Related Person',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/RelatedPerson/{id}',
                'description' => 'Memperbarui data elemen kerabat pasien secara parsial (sebagian) menggunakan format array JSON Patch (misalnya merubah nomor telepon secara cepat).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'RelatedPerson ID (Format: UUID)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',       // Operasi yang didukung SATUSEHAT: replace
                        'path' => '/telecom/0/value', // Target elemen nomor telepon dalam array struktur FHIR
                        'value' => '081599999999' // Nomor telepon pengganti baru
                    ]
                ],
            ],
        ]
    ],

    // =====================================================
    // CLAIM
    // =====================================================

    'claim' => [
        'label' => 'Claim',
        'icon' => '💰',
        'description' => 'Insurance claims',
        'endpoints' => [
            [
                'key' => 'create_claim',
                'label' => 'Create Claim',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Claim',
                'description' => 'Create insurance claim',
                'params' => [],
                'body' => [
                    'resourceType' => 'Claim',
                    'type' => ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/claim-type', 'code' => '']]],
                    'patient' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // CLAIM RESPONSE
    // =====================================================

    'claimresponse' => [
        'label' => 'ClaimResponse',
        'icon' => '💰',
        'description' => 'Claim responses and adjudications',
        'endpoints' => [
            [
                'key' => 'get_claimresponse',
                'label' => 'Get ClaimResponse',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ClaimResponse/{id}',
                'description' => 'Get claim response',
                'params' => [['name' => 'id', 'type' => 'text', 'placeholder' => 'ClaimResponse ID', 'default' => '']],
            ],
        ]
    ],

    // =====================================================
    // EXPLANATION OF BENEFIT
    // =====================================================

    'explanationofbenefit' => [
        'label' => 'ExplanationOfBenefit',
        'icon' => '💰',
        'description' => 'Explanation of benefits',
        'endpoints' => [
            [
                'key' => 'get_eob',
                'label' => 'Get ExplanationOfBenefit',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ExplanationOfBenefit?patient={patient_id}',
                'description' => 'Get EOB for patient',
                'params' => [['name' => 'patient_id', 'type' => 'text', 'placeholder' => 'Patient ID', 'default' => '']],
            ],
        ]
    ],

    // =====================================================
    // INVOICE
    // =====================================================

    'invoice' => [
        'label' => 'Invoice',
        'icon' => '🧾',
        'description' => 'Invoices and billing',
        'endpoints' => [
            [
                'key' => 'create_invoice',
                'label' => 'Create Invoice',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Invoice',
                'description' => 'Create invoice',
                'params' => [],
                'body' => [
                    'resourceType' => 'Invoice',
                    'status' => 'balanced',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // PAYMENT NOTICE
    // =====================================================

    'paymentnotice' => [
        'label' => 'PaymentNotice',
        'icon' => '💰',
        'description' => 'Payment notices',
        'endpoints' => [
            [
                'key' => 'create_paymentnotice',
                'label' => 'Create PaymentNotice',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/PaymentNotice',
                'description' => 'Create payment notice',
                'params' => [],
                'body' => [
                    'resourceType' => 'PaymentNotice',
                    'status' => 'paid',
                    'patient' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // PAYMENT RECONCILIATION
    // =====================================================

    'paymentreconciliation' => [
        'label' => 'PaymentReconciliation',
        'icon' => '💰',
        'description' => 'Payment reconciliation',
        'endpoints' => [
            [
                'key' => 'create_paymentrecon',
                'label' => 'Create PaymentReconciliation',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/PaymentReconciliation',
                'description' => 'Create payment reconciliation',
                'params' => [],
                'body' => [
                    'resourceType' => 'PaymentReconciliation',
                    'status' => 'completed'
                ]
            ],
        ]
    ],

    // =====================================================
    // BINARY
    // =====================================================

    'binary' => [
        'label' => 'Binary',
        'icon' => '📁',
        'description' => 'Binary data and attachments',
        'endpoints' => [
            [
                'key' => 'get_binary',
                'label' => 'Get Binary',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Binary/{id}',
                'description' => 'Get binary data',
                'params' => [['name' => 'id', 'type' => 'text', 'placeholder' => 'Binary ID', 'default' => '']],
            ],
        ]
    ],

    // =====================================================
    // COMMUNICATION
    // =====================================================

    'communication' => [
        'label' => 'Communication',
        'icon' => '💬',
        'description' => 'Communications and messages',
        'endpoints' => [
            [
                'key' => 'create_communication',
                'label' => 'Create Communication',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Communication',
                'description' => 'Create communication',
                'params' => [],
                'body' => [
                    'resourceType' => 'Communication',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // COMMUNICATION REQUEST
    // =====================================================

    'communicationrequest' => [
        'label' => 'CommunicationRequest',
        'icon' => '💬',
        'description' => 'Communication requests',
        'endpoints' => [
            [
                'key' => 'create_communicationrequest',
                'label' => 'Create CommunicationRequest',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/CommunicationRequest',
                'description' => 'Create communication request',
                'params' => [],
                'body' => [
                    'resourceType' => 'CommunicationRequest',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // FLAG
    // =====================================================

    'flag' => [
        'label' => 'Flag',
        'icon' => '🚩',
        'description' => 'Flags and alerts',
        'endpoints' => [
            [
                'key' => 'create_flag',
                'label' => 'Create Flag',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Flag',
                'description' => 'Create flag',
                'params' => [],
                'body' => [
                    'resourceType' => 'Flag',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // LIST
    // =====================================================

    'list' => [
        'label' => 'List',
        'icon' => '📋',
        'description' => 'Lists and collections',
        'endpoints' => [
            [
                'key' => 'create_list',
                'label' => 'Create List',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/List',
                'description' => 'Create list',
                'params' => [],
                'body' => [
                    'resourceType' => 'List',
                    'status' => 'current',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // IMMUNIZATION RECOMMENDATION
    // =====================================================

    'immunizationrecommendation' => [
        'label' => 'ImmunizationRecommendation',
        'icon' => '💉',
        'description' => 'Immunization recommendations',
        'endpoints' => [
            [
                'key' => 'get_immunizationrec',
                'label' => 'Get ImmunizationRecommendation',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ImmunizationRecommendation?patient={patient_id}',
                'description' => 'Get immunization recommendations',
                'params' => [['name' => 'patient_id', 'type' => 'text', 'placeholder' => 'Patient ID', 'default' => '']],
            ],
        ]
    ],

    // =====================================================
    // DEVICE
    // =====================================================

    'device' => [
        'label' => 'Device',
        'icon' => '📱',
        'description' => 'Medical devices and equipment',
        'endpoints' => [
            [
                'key' => 'get_device',
                'label' => 'Get Device',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Device',
                'description' => 'Get devices',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // DEVICE DEFINITION
    // =====================================================

    'devicedefinition' => [
        'label' => 'DeviceDefinition',
        'icon' => '📱',
        'description' => 'Device definitions and specifications',
        'endpoints' => [
            [
                'key' => 'get_devicedefinition',
                'label' => 'Get DeviceDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/DeviceDefinition',
                'description' => 'Get device definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // DEVICE METRIC
    // =====================================================

    'devicemetric' => [
        'label' => 'DeviceMetric',
        'icon' => '📊',
        'description' => 'Device metrics and measurements',
        'endpoints' => [
            [
                'key' => 'get_devicemetric',
                'label' => 'Get DeviceMetric',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/DeviceMetric',
                'description' => 'Get device metrics',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSTANCE
    // =====================================================

    'substance' => [
        'label' => 'Substance',
        'icon' => '🧪',
        'description' => 'Substances and chemicals',
        'endpoints' => [
            [
                'key' => 'get_substance',
                'label' => 'Get Substance',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Substance',
                'description' => 'Get substances',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // AUDIT EVENT
    // =====================================================

    'auditevent' => [
        'label' => 'AuditEvent',
        'icon' => '📝',
        'description' => 'Audit events and logs',
        'endpoints' => [
            [
                'key' => 'create_auditevent',
                'label' => 'Create AuditEvent',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/AuditEvent',
                'description' => 'Create audit event',
                'params' => [],
                'body' => [
                    'resourceType' => 'AuditEvent',
                    'type' => ['coding' => [['system' => '', 'code' => '', 'display' => '']]]
                ]
            ],
        ]
    ],

    // =====================================================
    // SUBSCRIPTION
    // =====================================================

    'subscription' => [
        'label' => 'Subscription',
        'icon' => '🔔',
        'description' => 'Subscriptions and notifications',
        'endpoints' => [
            [
                'key' => 'create_subscription',
                'label' => 'Create Subscription',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Subscription',
                'description' => 'Create subscription',
                'params' => [],
                'body' => [
                    'resourceType' => 'Subscription',
                    'status' => 'active',
                    'reason' => 'Monitoring'
                ]
            ],
        ]
    ],

    // =====================================================
    // CAPABILITY STATEMENT
    // =====================================================

    'capabilitystatement' => [
        'label' => 'CapabilityStatement',
        'icon' => '📋',
        'description' => 'Capability statements',
        'endpoints' => [
            [
                'key' => 'get_capability',
                'label' => 'Get CapabilityStatement',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CapabilityStatement/{id}',
                'description' => 'Get capability statement',
                'params' => [['name' => 'id', 'type' => 'text', 'placeholder' => 'ID', 'default' => '']],
            ],
        ]
    ],

    // =====================================================
    // SEARCH PARAMETER
    // =====================================================

    'searchparameter' => [
        'label' => 'SearchParameter',
        'icon' => '🔍',
        'description' => 'Search parameters',
        'endpoints' => [
            [
                'key' => 'get_searchparam',
                'label' => 'Get SearchParameter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SearchParameter',
                'description' => 'Get search parameters',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // CONCEPT MAP
    // =====================================================

    'conceptmap' => [
        'label' => 'ConceptMap',
        'icon' => '🗺️',
        'description' => 'Concept maps and translations',
        'endpoints' => [
            [
                'key' => 'get_conceptmap',
                'label' => 'Get ConceptMap',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ConceptMap',
                'description' => 'Get concept maps',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // NAMING SYSTEM
    // =====================================================

    'namingsystem' => [
        'label' => 'NamingSystem',
        'icon' => '📛',
        'description' => 'Naming systems',
        'endpoints' => [
            [
                'key' => 'get_namingsystem',
                'label' => 'Get NamingSystem',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/NamingSystem',
                'description' => 'Get naming systems',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // OPERATION DEFINITION
    // =====================================================

    'operationdefinition' => [
        'label' => 'OperationDefinition',
        'icon' => '⚙️',
        'description' => 'Operation definitions',
        'endpoints' => [
            [
                'key' => 'get_operationdef',
                'label' => 'Get OperationDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/OperationDefinition',
                'description' => 'Get operation definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // IMPLEMENTATION GUIDE
    // =====================================================

    'implementationguide' => [
        'label' => 'ImplementationGuide',
        'icon' => '📖',
        'description' => 'Implementation guides',
        'endpoints' => [
            [
                'key' => 'get_ig',
                'label' => 'Get ImplementationGuide',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ImplementationGuide',
                'description' => 'Get implementation guides',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // TERMINOLOGY CAPABILITIES
    // =====================================================

    'terminologycapabilities' => [
        'label' => 'TerminologyCapabilities',
        'icon' => '📚',
        'description' => 'Terminology capabilities',
        'endpoints' => [
            [
                'key' => 'get_terminologycap',
                'label' => 'Get TerminologyCapabilities',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/TerminologyCapabilities',
                'description' => 'Get terminology capabilities',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // COMPARTMENT DEFINITION
    // =====================================================

    'compartmentdefinition' => [
        'label' => 'CompartmentDefinition',
        'icon' => '📦',
        'description' => 'Compartment definitions',
        'endpoints' => [
            [
                'key' => 'get_compartment',
                'label' => 'Get CompartmentDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CompartmentDefinition',
                'description' => 'Get compartment definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // GRAPH DEFINITION
    // =====================================================

    'graphdefinition' => [
        'label' => 'GraphDefinition',
        'icon' => '🌐',
        'description' => 'Graph definitions',
        'endpoints' => [
            [
                'key' => 'get_graphdef',
                'label' => 'Get GraphDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/GraphDefinition',
                'description' => 'Get graph definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // NUTRITION ORDER
    // =====================================================

    'nutritionorder' => [
        'label' => 'NutritionOrder',
        'icon' => '🥗',
        'description' => 'Nutrition orders and diets',
        'endpoints' => [
            [
                'key' => 'create_nutritionorder',
                'label' => 'Create NutritionOrder',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/NutritionOrder',
                'description' => 'Create nutrition order',
                'params' => [],
                'body' => [
                    'resourceType' => 'NutritionOrder',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // VISION PRESCRIPTION
    // =====================================================

    'visionprescription' => [
        'label' => 'VisionPrescription',
        'icon' => '👓',
        'description' => 'Vision prescriptions',
        'endpoints' => [
            [
                'key' => 'create_visionprescription',
                'label' => 'Create VisionPrescription',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/VisionPrescription',
                'description' => 'Create vision prescription',
                'params' => [],
                'body' => [
                    'resourceType' => 'VisionPrescription',
                    'status' => 'active',
                    'patient' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // SUPPLY REQUEST
    // =====================================================

    'supplyrequest' => [
        'label' => 'SupplyRequest',
        'icon' => '📦',
        'description' => 'Supply requests',
        'endpoints' => [
            [
                'key' => 'create_supplyrequest',
                'label' => 'Create SupplyRequest',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/SupplyRequest',
                'description' => 'Create supply request',
                'params' => [],
                'body' => [
                    'resourceType' => 'SupplyRequest',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // SUPPLY DELIVERY
    // =====================================================

    'supplydelivery' => [
        'label' => 'SupplyDelivery',
        'icon' => '📦',
        'description' => 'Supply deliveries',
        'endpoints' => [
            [
                'key' => 'create_supplydelivery',
                'label' => 'Create SupplyDelivery',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/SupplyDelivery',
                'description' => 'Create supply delivery',
                'params' => [],
                'body' => [
                    'resourceType' => 'SupplyDelivery',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ],

    // =====================================================
    // RESEARCH STUDY
    // =====================================================

    'researchstudy' => [
        'label' => 'ResearchStudy',
        'icon' => '🔬',
        'description' => 'Research studies',
        'endpoints' => [
            [
                'key' => 'get_researchstudy',
                'label' => 'Get ResearchStudy',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ResearchStudy',
                'description' => 'Get research studies',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // RESEARCH SUBJECT
    // =====================================================

    'researchsubject' => [
        'label' => 'ResearchSubject',
        'icon' => '👨‍🔬',
        'description' => 'Research subjects',
        'endpoints' => [
            [
                'key' => 'get_researchsubject',
                'label' => 'Get ResearchSubject',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ResearchSubject',
                'description' => 'Get research subjects',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // PERSON
    // =====================================================

    'person' => [
        'label' => 'Person',
        'icon' => '👤',
        'description' => 'Persons and individuals',
        'endpoints' => [
            [
                'key' => 'get_person',
                'label' => 'Get Person',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Person',
                'description' => 'Get persons',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // PARAMETERS
    // =====================================================

    'parameters' => [
        'label' => 'Parameters',
        'icon' => '⚙️',
        'description' => 'Parameters and inputs',
        'endpoints' => [
            [
                'key' => 'get_parameters',
                'label' => 'Get Parameters',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Parameters',
                'description' => 'Get parameters',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // MEASURE
    // =====================================================

    'measure' => [
        'label' => 'Measure',
        'icon' => '📊',
        'description' => 'Measures and metrics',
        'endpoints' => [
            [
                'key' => 'get_measure',
                'label' => 'Get Measure',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Measure',
                'description' => 'Get measures',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // MEASURE REPORT
    // =====================================================

    'measurereport' => [
        'label' => 'MeasureReport',
        'icon' => '📊',
        'description' => 'Measure reports',
        'endpoints' => [
            [
                'key' => 'get_measurereport',
                'label' => 'Get MeasureReport',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MeasureReport',
                'description' => 'Get measure reports',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // LIBRARY
    // =====================================================

    'library' => [
        'label' => 'Library',
        'icon' => '📚',
        'description' => 'Libraries and knowledge',
        'endpoints' => [
            [
                'key' => 'get_library',
                'label' => 'Get Library',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Library',
                'description' => 'Get libraries',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // LINKAGE
    // =====================================================

    'linkage' => [
        'label' => 'Linkage',
        'icon' => '🔗',
        'description' => 'Linkages and associations',
        'endpoints' => [
            [
                'key' => 'get_linkage',
                'label' => 'Get Linkage',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Linkage',
                'description' => 'Get linkages',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // MESSAGE DEFINITION
    // =====================================================

    'messagedefinition' => [
        'label' => 'MessageDefinition',
        'icon' => '📧',
        'description' => 'Message definitions',
        'endpoints' => [
            [
                'key' => 'get_messagedef',
                'label' => 'Get MessageDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MessageDefinition',
                'description' => 'Get message definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // MESSAGE HEADER
    // =====================================================

    'messageheader' => [
        'label' => 'MessageHeader',
        'icon' => '📧',
        'description' => 'Message headers',
        'endpoints' => [
            [
                'key' => 'get_messageheader',
                'label' => 'Get MessageHeader',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MessageHeader',
                'description' => 'Get message headers',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // MOLECULAR SEQUENCE
    // =====================================================

    'molecularsequence' => [
        'label' => 'MolecularSequence',
        'icon' => '🧬',
        'description' => 'Molecular sequences',
        'endpoints' => [
            [
                'key' => 'get_molecularseq',
                'label' => 'Get MolecularSequence',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MolecularSequence',
                'description' => 'Get molecular sequences',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // OBSERVATION DEFINITION
    // =====================================================

    'observationdefinition' => [
        'label' => 'ObservationDefinition',
        'icon' => '📊',
        'description' => 'Observation definitions',
        'endpoints' => [
            [
                'key' => 'get_obsdef',
                'label' => 'Get ObservationDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ObservationDefinition',
                'description' => 'Get observation definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // OPERATION OUTCOME
    // =====================================================

    'operationoutcome' => [
        'label' => 'OperationOutcome',
        'icon' => '❌',
        'description' => 'Operation outcomes',
        'endpoints' => [
            [
                'key' => 'get_opoutcome',
                'label' => 'Get OperationOutcome',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/OperationOutcome',
                'description' => 'Get operation outcomes',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // ORGANIZATION AFFILIATION
    // =====================================================

    'organizationaffiliation' => [
        'label' => 'OrganizationAffiliation',
        'icon' => '🏢',
        'description' => 'Organization affiliations',
        'endpoints' => [
            [
                'key' => 'get_orgaffil',
                'label' => 'Get OrganizationAffiliation',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/OrganizationAffiliation',
                'description' => 'Get organization affiliations',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // PLAN DEFINITION
    // =====================================================

    'plandefinition' => [
        'label' => 'PlanDefinition',
        'icon' => '📋',
        'description' => 'Plan definitions',
        'endpoints' => [
            [
                'key' => 'get_plandef',
                'label' => 'Get PlanDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/PlanDefinition',
                'description' => 'Get plan definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // REQUEST GROUP
    // =====================================================

    'requestgroup' => [
        'label' => 'RequestGroup',
        'icon' => '📋',
        'description' => 'Request groups',
        'endpoints' => [
            [
                'key' => 'get_requestgroup',
                'label' => 'Get RequestGroup',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/RequestGroup',
                'description' => 'Get request groups',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // RESEARCH DEFINITION
    // =====================================================

    'researchdefinition' => [
        'label' => 'ResearchDefinition',
        'icon' => '🔬',
        'description' => 'Research definitions',
        'endpoints' => [
            [
                'key' => 'get_researchdef',
                'label' => 'Get ResearchDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ResearchDefinition',
                'description' => 'Get research definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // RESEARCH ELEMENT DEFINITION
    // =====================================================

    'researchelementdefinition' => [
        'label' => 'ResearchElementDefinition',
        'icon' => '🔬',
        'description' => 'Research element definitions',
        'endpoints' => [
            [
                'key' => 'get_researchelem',
                'label' => 'Get ResearchElementDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ResearchElementDefinition',
                'description' => 'Get research element definitions',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // RISK EVIDENCE SYNTHESIS
    // =====================================================

    'riskevidencesynthesis' => [
        'label' => 'RiskEvidenceSynthesis',
        'icon' => '⚠️',
        'description' => 'Risk evidence synthesis',
        'endpoints' => [
            [
                'key' => 'get_risksynth',
                'label' => 'Get RiskEvidenceSynthesis',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/RiskEvidenceSynthesis',
                'description' => 'Get risk evidence synthesis',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SPECIMEN DEFINITION
    // =====================================================

    // 'specimendefinition' => [
    //     'label' => 'SpecimenDefinition',
    //     'icon' => '🧪',
    //     'description' => 'Specimen definitions',
    //     'endpoints' => [
    //         [
    //             'key' => 'get_specimendef',
    //             'label' => 'Get SpecimenDefinition',
    //             'method' => 'GET',
    //             'path' => '/fhir-r4/v1/SpecimenDefinition',
    //             'description' => 'Get specimen definitions',
    //             'params' => [],
    //         ],
    //     ]
    // ],

    // =====================================================
    // STRUCTURE MAP
    // =====================================================

    'structuremap' => [
        'label' => 'StructureMap',
        'icon' => '🗺️',
        'description' => 'Structure maps',
        'endpoints' => [
            [
                'key' => 'get_structuremap',
                'label' => 'Get StructureMap',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/StructureMap',
                'description' => 'Get structure maps',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSCRIPTION STATUS
    // =====================================================

    'subscriptionstatus' => [
        'label' => 'SubscriptionStatus',
        'icon' => '🔔',
        'description' => 'Subscription statuses',
        'endpoints' => [
            [
                'key' => 'get_substatus',
                'label' => 'Get SubscriptionStatus',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubscriptionStatus',
                'description' => 'Get subscription statuses',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSCRIPTION TOPIC
    // =====================================================

    'subscriptiontopic' => [
        'label' => 'SubscriptionTopic',
        'icon' => '🔔',
        'description' => 'Subscription topics',
        'endpoints' => [
            [
                'key' => 'get_subtopic',
                'label' => 'Get SubscriptionTopic',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubscriptionTopic',
                'description' => 'Get subscription topics',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSTANCE DEFINITION
    // =====================================================

    // 'substancedefinition' => [
    //     'label' => 'SubstanceDefinition',
    //     'icon' => '🧪',
    //     'description' => 'Substance definitions',
    //     'endpoints' => [
    //         [
    //             'key' => 'get_substancedef',
    //             'label' => 'Get SubstanceDefinition',
    //             'method' => 'GET',
    //             'path' => '/fhir-r4/v1/SubstanceDefinition',
    //             'description' => 'Get substance definitions',
    //             'params' => [],
    //         ],
    //     ]
    // ],

    // =====================================================
    // SUBSTANCE NUCLEIC ACID
    // =====================================================

    'substantienucleicacid' => [
        'label' => 'SubstanceNucleicAcid',
        'icon' => '🧬',
        'description' => 'Substance nucleic acids',
        'endpoints' => [
            [
                'key' => 'get_subnucleic',
                'label' => 'Get SubstanceNucleicAcid',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceNucleicAcid',
                'description' => 'Get substance nucleic acids',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSTANCE POLYMER
    // =====================================================

    'substancepolymer' => [
        'label' => 'SubstancePolymer',
        'icon' => '🧬',
        'description' => 'Substance polymers',
        'endpoints' => [
            [
                'key' => 'get_subpolymer',
                'label' => 'Get SubstancePolymer',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstancePolymer',
                'description' => 'Get substance polymers',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSTANCE PROTEIN
    // =====================================================

    'substanceprotein' => [
        'label' => 'SubstanceProtein',
        'icon' => '🧬',
        'description' => 'Substance proteins',
        'endpoints' => [
            [
                'key' => 'get_subprotein',
                'label' => 'Get SubstanceProtein',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceProtein',
                'description' => 'Get substance proteins',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSTANCE REFERENCE INFORMATION
    // =====================================================

    'substantiereferenceinformation' => [
        'label' => 'SubstanceReferenceInformation',
        'icon' => '🧬',
        'description' => 'Substance reference information',
        'endpoints' => [
            [
                'key' => 'get_subrefinfo',
                'label' => 'Get SubstanceReferenceInformation',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceReferenceInformation',
                'description' => 'Get substance reference information',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSTANCE SOURCE MATERIAL
    // =====================================================

    'substancesourcematerial' => [
        'label' => 'SubstanceSourceMaterial',
        'icon' => '🧬',
        'description' => 'Substance source materials',
        'endpoints' => [
            [
                'key' => 'get_subsource',
                'label' => 'Get SubstanceSourceMaterial',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceSourceMaterial',
                'description' => 'Get substance source materials',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSTANCE SPECIFICATION
    // =====================================================

    'substancespecification' => [
        'label' => 'SubstanceSpecification',
        'icon' => '🧪',
        'description' => 'Substance specifications',
        'endpoints' => [
            [
                'key' => 'get_subspec',
                'label' => 'Get SubstanceSpecification',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceSpecification',
                'description' => 'Get substance specifications',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // TASK
    // =====================================================

    'task' => [
        'label' => 'Task',
        'icon' => '📋',
        'description' => 'Tasks and workflow management',
        'endpoints' => [
            [
                'key' => 'create_task',
                'label' => 'Create Task',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Task',
                'description' => 'Create task',
                'params' => [],
                'body' => [
                    'resourceType' => 'Task',
                    'status' => 'requested',
                    'intent' => 'order'
                ]
            ],
        ]
    ],

    // =====================================================
    // TEST REPORT
    // =====================================================

    'testreport' => [
        'label' => 'TestReport',
        'icon' => '🧪',
        'description' => 'Test reports',
        'endpoints' => [
            [
                'key' => 'get_testreport',
                'label' => 'Get TestReport',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/TestReport',
                'description' => 'Get test reports',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // TEST SCRIPT
    // =====================================================

    'testscript' => [
        'label' => 'TestScript',
        'icon' => '🧪',
        'description' => 'Test scripts',
        'endpoints' => [
            [
                'key' => 'get_testscript',
                'label' => 'Get TestScript',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/TestScript',
                'description' => 'Get test scripts',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // VERIFICATION RESULT
    // =====================================================

    'verificationresult' => [
        'label' => 'VerificationResult',
        'icon' => '✅',
        'description' => 'Verification results',
        'endpoints' => [
            [
                'key' => 'get_verify',
                'label' => 'Get VerificationResult',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/VerificationResult',
                'description' => 'Get verification results',
                'params' => [],
            ],
        ]
    ],

    // ==========================================
    // MASTER SARANA 
    // ==========================================

    'mastersarana' => [

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

    ],

    // ==========================================
    // MASTER WILAYAH 
    // ==========================================

    'masterwilayah' => [

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
    ],

    'masterkfa' => [

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
    ],

    'kyc' => [

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
    ]

];

$currentModule = $modules[$module] ?? $modules['patient'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATUSEHAT API Catalog - <?= $currentModule['label'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#3b82f6',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }
        .method-post { background-color: #dcfce7; color: #166534; }
        .method-put { background-color: #ffedd5; color: #9c271a; }
        .method-get { background-color: #dbeafe; color: #1e40af; }
        .method-patch { background-color: #fef3c7; color: #92400e; }
        .method-delete { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="gradient-bg text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-heartbeat text-2xl"></i>
                    <div>
                        <h1 class="text-xl font-bold">SATUSEHAT API Catalog</h1>
                        <p class="text-sm opacity-90">FHIR R4 Integration Platform</p>
                    </div>
                </div>
                <nav class="flex space-x-2">
                    <a href="index.php" class="px-4 py-2 bg-white bg-opacity-20 rounded-lg text-sm hover:bg-opacity-30">Home</a>
                    <a href="catalog.php" class="px-4 py-2 bg-white text-primary rounded-lg font-semibold text-sm">API Catalog</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 pt-20">
        <div class="flex gap-6">
            <!-- Sidebar - Modules -->
            <div class="w-64 flex-shrink-0">
                <div class="sticky top-20 z-30 space-y-4">
                    <div class="bg-white rounded-xl shadow p-4">
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Environment</label>
                            <select onchange="window.location.href='catalog.php?module=<?= $module ?>&env='+this.value" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-primary">
                                <option value="sandbox" <?= $currentEnv === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                                <option value="production" <?= $currentEnv === 'production' ? 'selected' : '' ?>>Production</option>
                            </select>
                        </div>
                        <div class="text-xs text-gray-500">
                            <div>Org: <?= htmlspecialchars(substr($org_id, 0, 20) . '...') ?></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-gray-800">Modules</h3>
                            <span class="text-xs text-gray-500"><?= count($modules) ?> modules</span>
                        </div>
                        
                        <div class="mb-3">
                            <input type="text" id="moduleSearch" placeholder="Search modules..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        </div>
                        
                        <div class="space-y-1 max-h-96 overflow-y-auto" id="moduleList">
                            <?php foreach ($modules as $key => $mod): ?>
                            <a href="catalog.php?module=<?= $key ?>&env=<?= $currentEnv ?>" class="module-item <?= $module === $key ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?> px-2 py-1.5 rounded text-sm flex items-center transition" data-label="<?= strtolower($mod['label']) ?>" data-key="<?= $key ?>">
                                <span class="mr-2"><?= $mod['icon'] ?></span>
                                <span class="truncate"><?= $mod['label'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Rate Limit Warning -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-yellow-600 mr-2 mt-0.5"></i>
                        <div>
                            <h4 class="font-semibold text-yellow-800">Important: SATUSEHAT API Rate Limits</h4>
                            <p class="text-yellow-700 text-sm mt-1">
                                The SATUSEHAT API has strict rate limits. If you encounter authentication errors, 
                                <strong>wait 1 minute</strong> before retrying. Rate limit: 1 request per minute after a failed attempt.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Current Module Header -->
                <div class="bg-white rounded-xl shadow p-6 mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-100 rounded-lg p-3">
                            <span class="text-3xl"><?= $currentModule['icon'] ?></span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800"><?= $currentModule['label'] ?></h2>
                            <p class="text-gray-600"><?= $currentModule['description'] ?></p>
                        </div>
                    </div>
                </div>

                <!-- Endpoints List -->
                <div class="space-y-6">
                    <?php foreach ($currentModule['endpoints'] as $endpoint): ?>
                    <div class="bg-white rounded-xl shadow overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800"><?= $endpoint['label'] ?></h3>
                                    <p class="text-gray-600 text-sm mt-1"><?= $endpoint['description'] ?></p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="px-3 py-1 rounded font-bold text-sm method-<?= strtolower($endpoint['method']) ?>">
                                        <?= $endpoint['method'] ?>
                                    </span>
                                    <code class="bg-gray-100 px-2 py-1 rounded text-sm"><?= $endpoint['path'] ?></code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <?php if ($endpoint['method'] === 'POST' || $endpoint['method'] === 'PUT' || $endpoint['method'] === 'PATCH'): ?>
                            <form method="POST" class="api-test-form space-y-4">
                                <input type="hidden" name="env" value="<?= $currentEnv ?>">
                                <input type="hidden" name="method" value="<?= $endpoint['method'] ?>">
                                <input type="hidden" name="path" value="<?= $endpoint['path'] ?>">
                                
                                <?php if (!empty($endpoint['params'])): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($endpoint['params'] as $param): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= $param['placeholder'] ?></label>
                                        <input type="text" name="params[<?= $param['name'] ?>]" value="<?= $param['default'] ?? '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Request Body (JSON)</label>
                                    <textarea name="body" rows="8" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono"><?= json_encode($endpoint['body'] ?? [], JSON_PRETTY_PRINT) ?></textarea>
                                </div>
                                
                                <button type="submit" class="bg-primary text-white px-6 py-2 rounded text-sm hover:bg-blue-700">
                                    <i class="fas fa-play mr-1"></i> Test API
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="GET" class="api-test-form space-y-4">
                                <input type="hidden" name="env" value="<?= $currentEnv ?>">
                                <input type="hidden" name="method" value="<?= $endpoint['method'] ?>">
                                <input type="hidden" name="path" value="<?= $endpoint['path'] ?>">
                                
                                <?php if (!empty($endpoint['params'])): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($endpoint['params'] as $param): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= $param['placeholder'] ?></label>
                                        <input type="text" name="params[<?= $param['name'] ?>]" value="<?= $param['default'] ?? '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <button type="submit" class="bg-primary text-white px-6 py-2 rounded text-sm hover:bg-blue-700">
                                    <i class="fas fa-play mr-1"></i> Test API
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t py-6 mt-12">
        <div class="container mx-auto px-4 text-center text-gray-500">
            <p>SATUSEHAT API Catalog Platform v1.0 | © <?= date('Y') ?> Kemenkes RI</p>
        </div>
    </footer>

    <!-- API Response Modal -->
    <div id="apiModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-bold text-gray-800">API Response</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-1 overflow-hidden">
                <div id="modalContent" class="p-4 max-h-[60vh] overflow-auto">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                        <p class="mt-2 text-gray-500">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t flex justify-end space-x-2">
                <button onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('apiModal').classList.remove('hidden');
            document.getElementById('apiModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('apiModal').classList.add('hidden');
            document.getElementById('apiModal').classList.remove('flex');
            document.body.style.overflow = '';
        }

        // Handle form submission via AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form.api-test-form');
            
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    const method = formData.get('method');
                    const path = formData.get('path');
                    const env = formData.get('env');
                    
                    // Show modal with loading
                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i><p class="mt-2 text-gray-500">Calling API...</p></div>';
                    openModal();
                    
                    // Build request body
                    let bodyData = null;
                    const bodyInput = form.querySelector('textarea[name="body"]');
                    if (bodyInput && bodyInput.value.trim()) {
                        try {
                            bodyData = JSON.parse(bodyInput.value);
                        } catch(e) {
                            bodyData = bodyInput.value;
                        }
                    }
                    
                    // Build params
                    const params = {};
                    const paramInputs = form.querySelectorAll('input[name^="params"]');
                    paramInputs.forEach(function(input) {
                        const name = input.name.replace('params[', '').replace(']', '');
                        params[name] = input.value;
                    });
                    
                    // Make API call
                    fetch('api_call.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            env: env,
                            method: method,
                            path: path,
                            params: params,
                            body: bodyData
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        let displayData = data.data;
                        if (data.data && typeof data.data === 'object') {
                            displayData = JSON.stringify(data.data, null, 2);
                        } else if (data.data && typeof data.data === 'string') {
                            displayData = data.data;
                        } else {
                            displayData = JSON.stringify(data, null, 2);
                        }
                        
                        const methodClass = method === 'POST' ? 'green' : 'blue';
                        const escapedDisplayData = escapeHtml(displayData);
                        modalContent.innerHTML = `
                            <div class="mb-3">
                                <span class="bg-${methodClass}-100 text-${methodClass}-800 px-3 py-1 rounded font-bold text-sm">
                                    ${method}
                                </span>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm ml-2">${data.path || path}</code>
                            </div>
                            <div class="bg-gray-900 rounded p-4 overflow-x-auto relative">
                                <button onclick="copyFromPre(this)" class="absolute top-2 right-2 bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                    <i class="fas fa-copy mr-1"></i> Copy
                                </button>
                                <pre id="responseContent" class="text-green-400 font-mono text-sm mt-6">${escapedDisplayData}</pre>
                            </div>
                        `;
                    })
                    .catch(error => {
                        const errorMsg = escapeHtml(error.message);
                        modalContent.innerHTML = `
                            <div class="bg-red-100 border border-red-200 rounded p-4 relative">
                                <button onclick="copyFromPre(this)" class="absolute top-2 right-2 bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                    <i class="fas fa-copy mr-1"></i> Copy
                                </button>
                                <div class="flex items-center mt-6">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                    <div>
                                        <h4 class="font-bold text-red-800">Error</h4>
                                        <pre class="text-red-700 text-sm">${errorMsg}</pre>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                });
            });
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Copy to clipboard function
        function copyFromPre(button) {
            // Find the pre element in the same container
            const preElement = button.closest('.bg-gray-900').querySelector('pre');
            const content = preElement.textContent || preElement.innerText;
            
            navigator.clipboard.writeText(content).then(function() {
                // Show success feedback
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
                button.classList.add('bg-green-600');
                button.classList.remove('bg-gray-700');
                setTimeout(function() {
                    button.innerHTML = originalHTML;
                    button.classList.remove('bg-green-600');
                    button.classList.add('bg-gray-700');
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                alert('Failed to copy content. Please try again.');
            });
        }

        // Module search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('moduleSearch');
            const moduleItems = document.querySelectorAll('.module-item');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    moduleItems.forEach(function(item) {
                        const label = item.getAttribute('data-label');
                        const key = item.getAttribute('data-key');
                        
                        if (label.includes(searchTerm) || key.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
