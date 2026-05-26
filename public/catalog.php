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

            [
                'key' => 'create_patient_neonates',
                'label' => 'Create Patient (Neonates/WNA Only)',
                'method' => 'POST',
                'path' => '/Patient',
                'description' => 'Hanya digunakan untuk pendaftaran Bayi Baru Lahir (belum memiliki NIK) atau WNA.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Patient',
                    'active' => true,
                    'name' => [
                        [
                            'use' => 'official',
                            'text' => 'Bayi Ny. S'
                        ]
                    ],
                    'gender' => 'female',
                    'birthDate' => date('Y-m-d'),
                    'multipleBirthBoolean' => true,
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
                'key' => 'get_patient',
                'label' => 'Get Patient by ID',
                'method' => 'GET',
                'path' => '/Patient/{id}',
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
                'path' => '/Patient?identifier=https://fhir.kemkes.go.id/id/nik|{nik}',
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
                'key' => 'search_patient_noka',
                'label' => 'Search Patient by Noka',
                'method' => 'GET',
                'path' => '/Patient?identifier=https://fhir.kemkes.go.id/id/noka|{noka}',
                'description' => 'Search patient by BPJS card number',
                'params' => [
                    [
                        'name' => 'noka',
                        'type' => 'text',
                        'placeholder' => 'Nomor Kartu BPJS',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'update_patient',
                'label' => 'Update Patient (Neonates/WNA Only)',
                'method' => 'PUT',
                'path' => '/Patient/{id}',
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
                'path' => '/Patient/{id}/_history',
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

            [
                'key' => 'create_encounter',
                'label' => 'Create Encounter (Arrived)',
                'method' => 'POST',
                'path' => '/Encounter',
                'description' => 'Membuat kunjungan awal pasien (wajib melampirkan Nakes, Lokasi, & Fasyankes).',
                'params' => [],
                'body' => [
                    'resourceType' => 'Encounter',
                    'status' => 'arrived',
                    'class' => [
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                        'code' => 'AMB',
                        'display' => 'ambulatory'
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien'
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
                                'reference' => 'Practitioner/{practitioner_id}',
                                'display' => 'Nama Dokter'
                            ]
                        ]
                    ],
                    'period' => [
                        'start' => '2026-05-26T09:00:00+07:00'
                    ],
                    'location' => [
                        [
                            'location' => [
                                'reference' => 'Location/{location_id}',
                                'display' => 'Poli Umum'
                            ]
                        ]
                    ],
                    'serviceProvider' => [
                        'reference' => 'Organization/{org_id}'
                    ]
                ]
            ],
            [
                'key' => 'update_encounter_status',
                'label' => 'Update Encounter (PUT/PATCH)',
                'method' => 'PUT',
                'path' => '/Encounter/{id}',
                'description' => 'Mengubah status kunjungan menjadi in-progress atau finished.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Encounter SATUSEHAT', 'default' => '']
                ],
                'body' => [
                    'resourceType' => 'Encounter',
                    'status' => 'finished',
                    'class' => [
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                        'code' => 'AMB',
                        'display' => 'ambulatory'
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ],
                    'period' => [
                        'start' => '2026-05-26T09:00:00+07:00',
                        'end' => '2026-05-26T09:30:00+07:00'
                    ],
                    'serviceProvider' => [
                        'reference' => 'Organization/{org_id}'
                    ]
                ]
            ],
            [
                'key' => 'get_encounter',
                'label' => 'Get Encounter by ID',
                'method' => 'GET',
                'path' => '/Encounter/{id}',
                'description' => 'Mengambil detail data kunjungan pasien.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Encounter', 'default' => '']
                ]
            ],

            [
                'key' => 'search_encounter',
                'label' => 'Search Encounter by Patient',
                'method' => 'GET',
                'path' => '/Encounter?subject={patient_id}',
                'description' => 'Search encounter',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
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

            [
                'key' => 'create_observation_vital',
                'label' => 'Create Vital Sign Observation',
                'method' => 'POST',
                'path' => '/Observation',
                'description' => 'Mencatat tanda vital seperti tekanan darah atau detak jantung (Wajib Tautan Encounter).',
                'params' => [],
                'body' => [
                    'resourceType' => 'Observation',
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
                        'reference' => 'Patient/{patient_id}'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ],
                    'effectiveDateTime' => '2026-05-26T09:15:00+07:00',
                    'valueQuantity' => [
                        'value' => 80,
                        'unit' => 'beats/min',
                        'system' => 'http://unitsofmeasure.org',
                        'code' => '/min'
                    ]
                ]
            ],
            [
                'key' => 'get_observation',
                'label' => 'Get Observation by ID',
                'method' => 'GET',
                'path' => '/Observation/{id}',
                'description' => 'Mengambil detail data observasi klinis.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'Observation ID', 'default' => '']
                ]
            ],

            [
                'key' => 'search_observation',
                'label' => 'Search Observation by Patient',
                'method' => 'GET',
                'path' => '/Observation?subject={patient_id}',
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
                'path' => '/Medication',
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
                'path' => '/Medication',
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
            [
                'key' => 'get_medication',
                'label' => 'Get Medication by ID',
                'method' => 'GET',
                'path' => '/Medication/{id}',
                'description' => 'Mengambil data master obat terdaftar.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'Medication ID', 'default' => '']
                ]
            ],
            [
                'key' => 'search_medication',
                'label' => 'Search Medication',
                'method' => 'GET',
                'path' => '/Medication?identifier={identifier}',
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
                'path' => '/Medication?status={status}',
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
            [
                'key' => 'update_medication',
                'label' => 'Update Medication',
                'method' => 'PUT',
                'path' => '/Medication/{id}',
                'description' => 'Memperbarui data detail sediaan obat (kamus obat lokal fasyankes) di SATUSEHAT. Harus mengirimkan seluruh data objek secara utuh.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Medication ID (e.g., 10000004)',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'Medication',
                    'id' => '{id}', // Wajib ada dan sama dengan ID di URL path
                    'meta' => [
                        'profile' => [
                            'https://fhir.kemkes.go.id/StructureDefinition/Medication'
                        ]
                    ],
                    'identifier' => [
                        [
                            'system' => 'http://sys-ids.kemkes.go.id/medication/10000004', // Ganti dengan Kode Fasyankes Anda
                            'value' => 'OBAT-001' // Kode obat internal SIMRS Anda
                        ]
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa', // Kamus Farmasi dan Alat Kesehatan Kemenkes
                                'code' => '93000104', // Contoh kode KFA untuk Amoxicillin 500 mg
                                'display' => 'Amoxicillin 500 mg Kaplet Salut Selaput'
                            ]
                        ]
                    ],
                    'status' => 'active', // Status obat saat ini (active / inactive / entered-in-error)
                    'form' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.kemkes.go.id/CodeSystem/medication-form',
                                'code' => 'BS019',
                                'display' => 'Kaplet Salut Selaput'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'key' => 'patch_medication',
                'label' => 'Patch Medication Status',
                'method' => 'PATCH',
                'path' => '/Medication/{id}',
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
                'path' => '/Medication/{id}/_history',
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
            //     'path' => '/Medication/_history',
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
            [
                'key' => 'create_medicationrequest',
                'label' => 'Create MedicationRequest',
                'method' => 'POST',
                'path' => '/MedicationRequest',
                'description' => 'Mengirim data peresepan obat oleh dokter (Wajib Tautan Encounter & Dokter).',
                'params' => [],
                'body' => [
                    'resourceType' => 'MedicationRequest',
                    'status' => 'active',
                    'intent' => 'order',
                    'medicationReference' => [
                        'reference' => 'Medication/{medication_id}',
                        'display' => 'Paracetamol 500 mg Tablet'
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ],
                    'authoredOn' => '2026-05-26T09:20:00+07:00',
                    'requester' => [
                        'reference' => 'Practitioner/{practitioner_id}',
                        'display' => 'Nama Dokter'
                    ]
                ]
            ],
            [
                'key' => 'get_medicationrequest',
                'label' => 'Get MedicationRequest by ID',
                'method' => 'GET',
                'path' => '/MedicationRequest/{id}',
                'description' => 'Mengambil detail data instruksi resep.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'MedicationRequest ID', 'default' => '']
                ]
            ],
            [
                'key' => 'search_medicationrequest',
                'label' => 'Search MedicationRequest by Patient',
                'method' => 'GET',
                'path' => '/MedicationRequest?subject={patient_id}',
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
            [
                'key' => 'update_medicationrequest',
                'label' => 'Update MedicationRequest',
                'method' => 'PUT',
                'path' => '/MedicationRequest/{id}',
                'description' => 'Update medication request',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationRequest ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'history_medicationrequest',
                'label' => 'History MedicationRequest',
                'method' => 'GET',
                'path' => '/MedicationRequest/{id}/_history',
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

            [
                'key' => 'patch_medicationrequest',
                'label' => 'Patch MedicationRequest',
                'method' => 'PATCH',
                'path' => '/MedicationRequest/{id}',
                'description' => 'Patch medication request',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationRequest ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_medicationrequest',
            //     'label' => 'History Type MedicationRequest',
            //     'method' => 'GET',
            //     'path' => '/MedicationRequest/_history',
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
                'key' => 'create_medicationdispense',
                'label' => 'Create MedicationDispense',
                'method' => 'POST',
                'path' => '/MedicationDispense',
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
                'path' => '/MedicationDispense/{id}',
                'description' => 'Mengambil detail data realisasi penyerahan obat.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'MedicationDispense ID', 'default' => '']
                ]
            ],
            // [
            //     'key' => 'create_medicationdispense',
            //     'label' => 'Create MedicationDispense',
            //     'method' => 'POST',
            //     'path' => '/MedicationDispense',
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
            //     'path' => '/MedicationDispense/{id}',
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
                'path' => '/MedicationDispense?subject={patient_id}',
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
                'path' => '/MedicationDispense/{id}',
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
            //     'path' => '/MedicationDispense/{id}',
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
                'path' => '/MedicationDispense/{id}/_history',
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
                'path' => '/MedicationDispense/{id}',
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
            //     'path' => '/MedicationDispense/_history',
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
                'path' => '/MedicationStatement',
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
                'path' => '/MedicationStatement/{id}',
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
                'path' => '/MedicationStatement?subject={patient_id}',
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
                'path' => '/MedicationStatement/{id}',
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
            //     'path' => '/MedicationStatement/{id}',
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
                'path' => '/MedicationStatement/{id}/_history',
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
                'path' => '/MedicationStatement/{id}',
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
            //     'path' => '/MedicationStatement/_history',
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
                'key' => 'create_service_request',
                'label' => 'Create ServiceRequest',
                'method' => 'POST',
                'path' => '/ServiceRequest',
                'description' => 'Create service request',
                'params' => [],
                'body' => [
                    'resourceType' => 'ServiceRequest',
                    'status' => 'active',
                    'intent' => 'order',
                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '',
                                'display' => ''
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'get_service_request',
                'label' => 'Get ServiceRequest',
                'method' => 'GET',
                'path' => '/ServiceRequest/{id}',
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
                'path' => '/Immunization?patient={patient_id}',
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

            [
                'key' => 'create_immunization',
                'label' => 'Create Immunization',
                'method' => 'POST',
                'path' => '/Immunization',
                'description' => 'Mencatat pemberian vaksin/imunisasi baru pada pasien sesuai standarisasi profil SATUSEHAT Kemenkes.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Immunization',
                    'status' => 'completed',
                    'vaccineCode' => [
                        'coding' => [
                            [
                                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                                'code' => '93001019',
                                'display' => 'Vaksin Hepatitis B Rekombinan 0.5 mL'
                            ]
                        ]
                    ],
                    'patient' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}',
                        'display' => 'Kunjungan Pemeriksaan'
                    ],
                    'occurrenceDateTime' => date('c'),
                    'primarySource' => true,
                    'performer' => [
                        [
                            'actor' => [
                                'reference' => 'Practitioner/{practitioner_id}',
                                'display' => 'Nama Tenaga Kesehatan'
                            ]
                        ]
                    ],
                    'reasonCode' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                                    'code' => 'Z24.4',
                                    'display' => 'Need for immunization against viral hepatitis'
                                ]
                            ]
                        ]
                    ],
                    'protocolApplied' => [
                        [
                            'doseNumberPositiveInt' => 1
                        ]
                    ]
                ]
            ],
            [
                'key' => 'get_immunization',
                'label' => 'Get Immunization by ID',
                'method' => 'GET',
                'path' => '/Immunization/{id}',
                'description' => 'Mengambil detail data riwayat imunisasi berdasarkan ID.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'Immunization ID', 'default' => '']
                ]
            ]

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
                'key' => 'create_diagnostic_report',
                'label' => 'Create DiagnosticReport',
                'method' => 'POST',
                'path' => '/DiagnosticReport',
                'description' => 'Create diagnostic report',
                'params' => [],
                'body' => [
                    'resourceType' => 'DiagnosticReport',
                    'status' => 'final',

                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v2-0074',
                                    'code' => 'LAB'
                                ]
                            ]
                        ]
                    ],

                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
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

            [
                'key' => 'create_procedure',
                'label' => 'Create Procedure',
                'method' => 'POST',
                'path' => '/Procedure',
                'description' => 'Create procedure',
                'params' => [],
                'body' => [
                    'resourceType' => 'Procedure',
                    'status' => 'completed',
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
                                'code' => '',
                                'display' => ''
                            ]
                        ]
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
            ],

            [
                'key' => 'get_procedure',
                'label' => 'Get Procedure by ID',
                'method' => 'GET',
                'path' => '/Procedure/{id}',
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
                'path' => '/Procedure?subject={patient_id}',
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
                'key' => 'update_procedure',
                'label' => 'Update Procedure',
                'method' => 'PUT',
                'path' => '/Procedure/{id}',
                'description' => 'Update procedure',
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
                'key' => 'patch_procedure',
                'label' => 'Patch Procedure',
                'method' => 'PATCH',
                'path' => '/Procedure/{id}',
                'description' => 'Patch procedure',
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
                'key' => 'delete_procedure',
                'label' => 'Delete Procedure',
                'method' => 'DELETE',
                'path' => '/Procedure/{id}',
                'description' => 'Delete procedure',
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
                'key' => 'history_procedure',
                'label' => 'History Procedure',
                'method' => 'GET',
                'path' => '/Procedure/{id}/_history',
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

            // [
            //     'key' => 'history_type_procedure',
            //     'label' => 'History Type Procedure',
            //     'method' => 'GET',
            //     'path' => '/Procedure/_history',
            //     'description' => 'Get all procedure history',
            //     'params' => [],
            // ],

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
                'key' => 'create_specimen',
                'label' => 'Create Specimen',
                'method' => 'POST',
                'path' => '/Specimen',
                'description' => 'Create specimen',
                'params' => [],
                'body' => [
                    'resourceType' => 'Specimen',
                    'status' => 'available',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
            [
                'key' => 'get_specimen',
                'label' => 'Get Specimen by ID',
                'method' => 'GET',
                'path' => '/Specimen/{id}',
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
            [
                'key' => 'search_specimen',
                'label' => 'Search Specimen by Patient',
                'method' => 'GET',
                'path' => '/Specimen?subject={patient_id}',
                'description' => 'Search specimens for a patient',
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
                'key' => 'update_specimen',
                'label' => 'Update Specimen',
                'method' => 'PUT',
                'path' => '/Specimen/{id}',
                'description' => 'Update specimen',
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
                'key' => 'delete_specimen',
                'label' => 'Delete Specimen',
                'method' => 'DELETE',
                'path' => '/Specimen/{id}',
                'description' => 'Delete specimen',
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
                'key' => 'history_specimen',
                'label' => 'History Specimen',
                'method' => 'GET',
                'path' => '/Specimen/{id}/_history',
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
                'path' => '/Specimen/{id}',
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
            //     'path' => '/Specimen/_history',
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
                'key' => 'create_imaging_study',
                'label' => 'Create ImagingStudy',
                'method' => 'POST',
                'path' => '/ImagingStudy',
                'description' => 'Create imaging study',
                'params' => [],
                'body' => [
                    'resourceType' => 'ImagingStudy',
                    'status' => 'available',

                    'patient' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
            ],

            [
                'key' => 'search_imaging_study',
                'label' => 'Search ImagingStudy',
                'method' => 'GET',
                'path' => '/ImagingStudy?patient={patient_id}',
                'description' => 'Search imaging study',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'Patient ID',
                        'default' => ''
                    ]
                ],
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

            [
                'key' => 'create_appointment',
                'label' => 'Create Appointment',
                'method' => 'POST',
                'path' => '/Appointment',
                'description' => 'Create appointment',
                'params' => [],
                'body' => [
                    'resourceType' => 'Appointment',
                    'status' => 'booked|need-actions',

                    'participant' => [
                        [
                            'actor' => [
                                'reference' => 'Patient/{patient_id}'
                            ],
                            'status' => 'accepted'
                        ]
                    ]
                ]
            ],

            [
                'key' => 'get_appointment',
                'label' => 'Get Appointment by ID',
                'method' => 'GET',
                'path' => '/Appointment/{id}',
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
                'path' => '/Appointment?patient={patient_id}',
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

            [
                'key' => 'update_appointment',
                'label' => 'Update Appointment',
                'method' => 'PUT',
                'path' => '/Appointment/{id}',
                'description' => 'Update appointment',
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
                'key' => 'delete_appointment',
                'label' => 'Delete Appointment',
                'method' => 'DELETE',
                'path' => '/Appointment/{id}',
                'description' => 'Delete appointment',
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
                'key' => 'history_appointment',
                'label' => 'History Appointment',
                'method' => 'GET',
                'path' => '/Appointment/{id}/_history',
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
            //     'path' => '/Appointment/_history',
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
                'path' => '/CarePlan',
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
                'path' => '/CarePlan/{id}',
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
                'path' => '/CarePlan?subject={patient_id}',
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
                'path' => '/CarePlan/{id}',
                'description' => 'Update care plan',
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
                'key' => 'delete_careplan',
                'label' => 'Delete CarePlan',
                'method' => 'DELETE',
                'path' => '/CarePlan/{id}',
                'description' => 'Delete care plan',
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
                'key' => 'history_careplan',
                'label' => 'History CarePlan',
                'method' => 'GET',
                'path' => '/CarePlan/{id}/_history',
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
            //     'path' => '/CarePlan/_history',
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
                'path' => '/DocumentReference',
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
                'path' => '/Consent',
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
                'path' => '/Consent/{id}',
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
                'path' => '/Consent?patient={patient_id}',
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
                'path' => '/Consent/{id}',
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
                'path' => '/Consent/{id}',
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
                'path' => '/Consent/{id}/_history',
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
            //     'path' => '/Consent/_history',
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
                'path' => '/Coverage',
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
                'path' => '/Coverage/{id}',
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
                'path' => '/Coverage?beneficiary={patient_id}',
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
                'path' => '/Coverage/{id}',
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
                'path' => '/Coverage/{id}',
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
                'path' => '/Coverage/{id}/_history',
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
            //     'path' => '/Coverage/_history',
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
                'path' => '/Organization/{id}',
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
                'path' => '/Organization?name={name}',
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
                'key' => 'create_organization',
                'label' => 'Create Organization',
                'method' => 'POST',
                'path' => '/Organization',
                'description' => 'Create organization',
                'params' => [],
                'body' => [
                    'resourceType' => 'Organization',
                    'name' => '',
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                                'code' => 'prov',
                                'display' => 'Healthcare Provider'
                            ]
                        ]
                    ]
                ]
            ],

            [
                'key' => 'update_organization',
                'label' => 'Update Organization',
                'method' => 'PUT',
                'path' => '/Organization/{id}',
                'description' => 'Update organization',
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
                'key' => 'delete_organization',
                'label' => 'Delete Organization',
                'method' => 'DELETE',
                'path' => '/Organization/{id}',
                'description' => 'Delete organization',
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
                'key' => 'history_organization',
                'label' => 'History Organization',
                'method' => 'GET',
                'path' => '/Organization/{id}/_history',
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

            // [
            //     'key' => 'history_type_organization',
            //     'label' => 'History Type Organization',
            //     'method' => 'GET',
            //     'path' => '/Organization/_history',
            //     'description' => 'Get all organization history',
            //     'params' => [],
            // ],
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
                'path' => '/Location',
                'description' => 'Mendaftarkan ruangan/poliklinik baru di fasyankes.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Location',
                    'identifier' => [
                        [
                            'system' => 'http://sys-ids.kemkes.go.id/location/{org_id}',
                            'value' => 'G-Poli-Umum'
                        ]
                    ],
                    'status' => 'active',
                    'name' => 'Ruang Poli Umum',
                    'description' => 'Ruang Pemeriksaan Poli Umum Gedung A Lantai 1',
                    'mode' => 'instance',
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
                        'reference' => 'Organization/{org_id}'
                    ]
                ]
            ],
            [
                'key' => 'get_location',
                'label' => 'Get Location by ID',
                'method' => 'GET',
                'path' => '/Location/{id}',
                'description' => 'Mengambil data spesifik ruangan berdasarkan ID Location.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Location SATUSEHAT', 'default' => '']
                ]
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
                'path' => '/Practitioner?identifier=https://fhir.kemkes.go.id/id/nik|{nik}',
                'description' => 'Mencari ID SATUSEHAT Tenaga Kesehatan / Dokter berdasarkan NIK.',
                'params' => [
                    ['name' => 'nik', 'type' => 'text', 'placeholder' => 'Masukkan NIK Dokter', 'default' => '']
                ]
            ],
            [
                'key' => 'get_practitioner_id',
                'label' => 'Get Practitioner by ID',
                'method' => 'GET',
                'path' => '/Practitioner/{id}',
                'description' => 'Mengambil data profil dokter berdasarkan ID SATUSEHAT.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Practitioner SATUSEHAT', 'default' => '']
                ]
            ],

            [
                'key' => 'get_practitioner',
                'label' => 'Get Practitioner',
                'method' => 'GET',
                'path' => '/Practitioner/{id}',
                'description' => 'Get practitioner',
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
            //     'key' => 'search_practitioner',
            //     'label' => 'Search Practitioner',
            //     'method' => 'GET',
            //     'path' => '/Practitioner?given={given}&family={family}',
            //     'description' => 'Search practitioners',
            //     'params' => [
            //         [
            //             'name' => 'given',
            //             'type' => 'text',
            //             'placeholder' => 'Given Name',
            //             'default' => ''
            //         ],
            //         [
            //             'name' => 'family',
            //             'type' => 'text',
            //             'placeholder' => 'Family Name',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            // [
            //     'key' => 'create_practitioner',
            //     'label' => 'Create Practitioner',
            //     'method' => 'POST',
            //     'path' => '/Practitioner',
            //     'description' => 'Create practitioner',
            //     'params' => [],
            //     'body' => [
            //         'resourceType' => 'Practitioner',
            //         'name' => [
            //             [
            //                 'given' => [''],
            //                 'family' => ''
            //             ]
            //         ]
            //     ]
            // ],

            // [
            //     'key' => 'update_practitioner',
            //     'label' => 'Update Practitioner',
            //     'method' => 'PUT',
            //     'path' => '/Practitioner/{id}',
            //     'description' => 'Update practitioner',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Practitioner ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            // [
            //     'key' => 'delete_practitioner',
            //     'label' => 'Delete Practitioner',
            //     'method' => 'DELETE',
            //     'path' => '/Practitioner/{id}',
            //     'description' => 'Delete practitioner',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'Practitioner ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_practitioner',
                'label' => 'History Practitioner',
                'method' => 'GET',
                'path' => '/Practitioner/{id}/_history',
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
            //     'path' => '/Practitioner/_history',
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

            [
                'key' => 'create_episode_of_care',
                'label' => 'Create EpisodeOfCare',
                'method' => 'POST',
                'path' => '/EpisodeOfCare',
                'description' => 'Create episode of care',
                'params' => [],
                'body' => [
                    'resourceType' => 'EpisodeOfCare',
                    'status' => 'active',

                    'patient' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
            ],

            [
                'key' => 'get_episode_of_care',
                'label' => 'Get EpisodeOfCare by ID',
                'method' => 'GET',
                'path' => '/EpisodeOfCare/{id}',
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

            [
                'key' => 'search_episode_of_care',
                'label' => 'Search EpisodeOfCare by Patient',
                'method' => 'GET',
                'path' => '/EpisodeOfCare?patient={patient_id}',
                'description' => 'Search episodes of care for patient',
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
                'key' => 'update_episode_of_care',
                'label' => 'Update EpisodeOfCare',
                'method' => 'PUT',
                'path' => '/EpisodeOfCare/{id}',
                'description' => 'Update episode of care',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'delete_episode_of_care',
                'label' => 'Delete EpisodeOfCare',
                'method' => 'DELETE',
                'path' => '/EpisodeOfCare/{id}',
                'description' => 'Delete episode of care',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'EpisodeOfCare ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'history_episode_of_care',
                'label' => 'History EpisodeOfCare',
                'method' => 'GET',
                'path' => '/EpisodeOfCare/{id}/_history',
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
            //     'path' => '/EpisodeOfCare/_history',
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
                'path' => '/',
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
                'path' => '/Provenance',
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
                'path' => '/Provenance/{id}',
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
                'path' => '/Provenance?target={target_id}',
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
                'path' => '/Provenance/{id}',
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
                'path' => '/Provenance/{id}',
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
                'path' => '/Provenance/{id}/_history',
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
            //     'path' => '/Provenance/_history',
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
                'path' => '/CareTeam',
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
                'path' => '/CareTeam?subject={patient_id}',
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
                'path' => '/DetectedIssue',
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
            [
                'key' => 'create_clinicalimpression',
                'label' => 'Create ClinicalImpression',
                'method' => 'POST',
                'path' => '/ClinicalImpression',
                'description' => 'Create clinical impression',
                'params' => [],
                'body' => [
                    'resourceType' => 'ClinicalImpression',
                    'status' => 'completed',
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/clinicalimpression',
                                'code' => '',
                                'display' => ''
                            ]
                        ]
                    ],
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
            [
                'key' => 'get_clinicalimpression',
                'label' => 'Get ClinicalImpression by ID',
                'method' => 'GET',
                'path' => '/ClinicalImpression/{id}',
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
                'path' => '/ClinicalImpression?subject={patient_id}',
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
            [
                'key' => 'update_clinicalimpression',
                'label' => 'Update ClinicalImpression',
                'method' => 'PUT',
                'path' => '/ClinicalImpression/{id}',
                'description' => 'Update clinical impression',
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
                'key' => 'delete_clinicalimpression',
                'label' => 'Delete ClinicalImpression',
                'method' => 'DELETE',
                'path' => '/ClinicalImpression/{id}',
                'description' => 'Delete clinical impression',
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
                'key' => 'history_clinicalimpression',
                'label' => 'History ClinicalImpression',
                'method' => 'GET',
                'path' => '/ClinicalImpression/{id}/_history',
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
            //     'path' => '/ClinicalImpression/_history',
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
                'path' => '/CodeSystem',
                'description' => 'Get code systems',
                'params' => [],
            ],
            [
                'key' => 'get_codesystem_by_id',
                'label' => 'Get CodeSystem by ID',
                'method' => 'GET',
                'path' => '/CodeSystem/{id}',
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
                'path' => '/CodeSystem?url={url}',
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
            [
                'key' => 'create_composition',
                'label' => 'Create Composition',
                'method' => 'POST',
                'path' => '/Composition',
                'description' => 'Create composition',
                'params' => [],
                'body' => [
                    'resourceType' => 'Composition',
                    'status' => 'final',
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/composition-type',
                                'code' => 'summary',
                                'display' => 'Summary'
                            ]
                        ]
                    ],
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
            [
                'key' => 'get_composition',
                'label' => 'Get Composition by ID',
                'method' => 'GET',
                'path' => '/Composition/{id}',
                'description' => 'Get composition by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Composition ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'search_composition',
                'label' => 'Search Composition by Patient',
                'method' => 'GET',
                'path' => '/Composition?subject={patient_id}',
                'description' => 'Search compositions for patient',
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
                'key' => 'update_composition',
                'label' => 'Update Composition',
                'method' => 'PUT',
                'path' => '/Composition/{id}',
                'description' => 'Update composition',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Composition ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'delete_composition',
                'label' => 'Delete Composition',
                'method' => 'DELETE',
                'path' => '/Composition/{id}',
                'description' => 'Delete composition',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Composition ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'history_composition',
                'label' => 'History Composition',
                'method' => 'GET',
                'path' => '/Composition/{id}/_history',
                'description' => 'Get composition history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Composition ID',
                        'default' => ''
                    ]
                ],
            ],
            // [
            //     'key' => 'history_type_composition',
            //     'label' => 'History Type Composition',
            //     'method' => 'GET',
            //     'path' => '/Composition/_history',
            //     'description' => 'Get all composition history',
            //     'params' => [],
            // ],
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
                'path' => '/SubstanceNucleicAcid',
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
                'path' => '/AdverseEvent',
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

            [
                'key' => 'create_allergyintolerance',
                'label' => 'Create AllergyIntolerance',
                'method' => 'POST',
                'path' => '/AllergyIntolerance',
                'description' => 'Create allergy',
                'params' => [],
                'body' => [
                    'resourceType' => 'AllergyIntolerance',

                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
                                'code' => 'active'
                            ]
                        ]
                    ],

                    'verificationStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
                                'code' => 'confirmed'
                            ]
                        ]
                    ],

                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '',
                                'display' => ''
                            ]
                        ]
                    ],

                    'patient' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
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
            [
                'key' => 'create_condition',
                'label' => 'Create Condition (Diagnosa)',
                'method' => 'POST',
                'path' => '/Condition',
                'description' => 'Mengirimkan diagnosa utama/sekunder pasien menggunakan standar ICD-10.',
                'params' => [],
                'body' => [
                    'resourceType' => 'Condition',
                    'clinicalStatus' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                                'code' => 'active',
                                'display' => 'Active'
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
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ]
                ]
            ],
            [
                'key' => 'get_condition_id',
                'label' => 'Get Condition by ID',
                'method' => 'GET',
                'path' => '/Condition/{id}',
                'description' => 'Mengambil data kondisi penyakit berdasarkan ID.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'ID Condition SATUSEHAT', 'default' => '']
                ]
            ],
            // [
            //     'key' => 'create_condition',
            //     'label' => 'Create Condition',
            //     'method' => 'POST',
            //     'path' => '/Condition',
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
            //     'path' => '/Condition/{id}',
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
                'path' => '/Condition?subject={patient_id}',
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
            [
                'key' => 'create_appointmentresponse',
                'label' => 'Create AppointmentResponse',
                'method' => 'POST',
                'path' => '/AppointmentResponse',
                'description' => 'Create appointment response',
                'params' => [],
                'body' => [
                    'resourceType' => 'AppointmentResponse',
                    'status' => 'accepted',
                    'appointment' => ['reference' => 'Appointment/{appointment_id}']
                ]
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
                'path' => '/Contract',
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
                'path' => '/Schedule',
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
                'path' => '/Slot',
                'description' => 'Get slots',
                'params' => [],
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
                'path' => '/StructureDefinition',
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
                'path' => '/ValueSet',
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
                'path' => '/Questionnaire',
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
            [
                'key' => 'create_questionnaireresponse',
                'label' => 'Create QuestionnaireResponse',
                'method' => 'POST',
                'path' => '/QuestionnaireResponse',
                'description' => 'Create questionnaire response',
                'params' => [],
                'body' => [
                    'resourceType' => 'QuestionnaireResponse',
                    'status' => 'completed',
                    'questionnaire' => ['reference' => 'Questionnaire/{questionnaire_id}'],
                    'subject' => ['reference' => 'Patient/{patient_id}']
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
                'path' => '/RiskAssessment',
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
            [
                'key' => 'create_familymemberhistory',
                'label' => 'Create FamilyMemberHistory',
                'method' => 'POST',
                'path' => '/FamilyMemberHistory',
                'description' => 'Create family member history',
                'params' => [],
                'body' => [
                    'resourceType' => 'FamilyMemberHistory',
                    'status' => 'completed',
                    'patient' => ['reference' => 'Patient/{patient_id}']
                ]
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
                'path' => '/Goal',
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
                'path' => '/MedicationAdministration?subject={patient_id}',
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
                'path' => '/MedicationKnowledge/{id}',
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
                'path' => '/Media',
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
                'path' => '/HealthcareService',
                'description' => 'Get healthcare services',
                'params' => [],
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
                'path' => '/Endpoint',
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
                'path' => '/PractitionerRole',
                'description' => 'Get practitioner roles',
                'params' => [],
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
            [
                'key' => 'create_relatedperson',
                'label' => 'Create RelatedPerson',
                'method' => 'POST',
                'path' => '/RelatedPerson',
                'description' => 'Create related person',
                'params' => [],
                'body' => [
                    'resourceType' => 'RelatedPerson',
                    'patient' => ['reference' => 'Patient/{patient_id}']
                ]
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
                'path' => '/Claim',
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
                'path' => '/ClaimResponse/{id}',
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
                'path' => '/ExplanationOfBenefit?patient={patient_id}',
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
                'path' => '/Invoice',
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
                'path' => '/PaymentNotice',
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
                'path' => '/PaymentReconciliation',
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
                'path' => '/Binary/{id}',
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
                'path' => '/Communication',
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
                'path' => '/CommunicationRequest',
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
                'path' => '/Flag',
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
                'path' => '/List',
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
                'path' => '/ImmunizationRecommendation?patient={patient_id}',
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
                'path' => '/Device',
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
                'path' => '/DeviceDefinition',
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
                'path' => '/DeviceMetric',
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
                'path' => '/Substance',
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
                'path' => '/AuditEvent',
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
                'path' => '/Subscription',
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
                'path' => '/CapabilityStatement/{id}',
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
                'path' => '/SearchParameter',
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
                'path' => '/ConceptMap',
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
                'path' => '/NamingSystem',
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
                'path' => '/OperationDefinition',
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
                'path' => '/ImplementationGuide',
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
                'path' => '/TerminologyCapabilities',
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
                'path' => '/CompartmentDefinition',
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
                'path' => '/GraphDefinition',
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
                'path' => '/NutritionOrder',
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
                'path' => '/VisionPrescription',
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
                'path' => '/SupplyRequest',
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
                'path' => '/SupplyDelivery',
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
                'path' => '/ResearchStudy',
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
                'path' => '/ResearchSubject',
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
                'path' => '/Person',
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
                'path' => '/Parameters',
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
                'path' => '/Measure',
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
                'path' => '/MeasureReport',
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
                'path' => '/Library',
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
                'path' => '/Linkage',
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
                'path' => '/MessageDefinition',
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
                'path' => '/MessageHeader',
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
                'path' => '/MolecularSequence',
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
                'path' => '/ObservationDefinition',
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
                'path' => '/OperationOutcome',
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
                'path' => '/OrganizationAffiliation',
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
                'path' => '/PlanDefinition',
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
                'path' => '/RequestGroup',
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
                'path' => '/ResearchDefinition',
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
                'path' => '/ResearchElementDefinition',
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
                'path' => '/RiskEvidenceSynthesis',
                'description' => 'Get risk evidence synthesis',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SPECIMEN DEFINITION
    // =====================================================

    'specimendefinition' => [
        'label' => 'SpecimenDefinition',
        'icon' => '🧪',
        'description' => 'Specimen definitions',
        'endpoints' => [
            [
                'key' => 'get_specimendef',
                'label' => 'Get SpecimenDefinition',
                'method' => 'GET',
                'path' => '/SpecimenDefinition',
                'description' => 'Get specimen definitions',
                'params' => [],
            ],
        ]
    ],

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
                'path' => '/StructureMap',
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
                'path' => '/SubscriptionStatus',
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
                'path' => '/SubscriptionTopic',
                'description' => 'Get subscription topics',
                'params' => [],
            ],
        ]
    ],

    // =====================================================
    // SUBSTANCE DEFINITION
    // =====================================================

    'substancedefinition' => [
        'label' => 'SubstanceDefinition',
        'icon' => '🧪',
        'description' => 'Substance definitions',
        'endpoints' => [
            [
                'key' => 'get_substancedef',
                'label' => 'Get SubstanceDefinition',
                'method' => 'GET',
                'path' => '/SubstanceDefinition',
                'description' => 'Get substance definitions',
                'params' => [],
            ],
        ]
    ],

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
                'path' => '/SubstanceNucleicAcid',
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
                'path' => '/SubstancePolymer',
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
                'path' => '/SubstanceProtein',
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
                'path' => '/SubstanceReferenceInformation',
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
                'path' => '/SubstanceSourceMaterial',
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
                'path' => '/SubstanceSpecification',
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
                'path' => '/Task',
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
                'path' => '/TestReport',
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
                'path' => '/TestScript',
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
                'path' => '/VerificationResult',
                'description' => 'Get verification results',
                'params' => [],
            ],
        ]
    ],

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
                            <?php if ($endpoint['method'] === 'POST' || $endpoint['method'] === 'PUT'): ?>
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
                        modalContent.innerHTML = `
                            <div class="mb-3">
                                <span class="bg-${methodClass}-100 text-${methodClass}-800 px-3 py-1 rounded font-bold text-sm">
                                    ${method}
                                </span>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm ml-2">${data.path || path}</code>
                            </div>
                            <div class="bg-gray-900 rounded p-4 overflow-x-auto">
                                <pre class="text-green-400 font-mono text-sm">${escapeHtml(displayData)}</pre>
                            </div>
                        `;
                    })
                    .catch(error => {
                        modalContent.innerHTML = `
                            <div class="bg-red-100 border border-red-200 rounded p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                    <div>
                                        <h4 class="font-bold text-red-800">Error</h4>
                                        <p class="text-red-700 text-sm">${escapeHtml(error.message)}</p>
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
