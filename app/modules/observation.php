<?php 
    // =====================================================
    // OBSERVATION
    // =====================================================

    return [
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
                        'reference' => 'Patient/{patient_id}', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}', // Mengikat ID Encounter kunjungan terkait
                        'display' => 'Kunjungan Pemeriksaan Fisik Terkait'
                    ],
                    'effectiveDateTime' => '2026-05-29T10:00:00+07:00', // Waktu saat pemeriksaan/pengukuran dilakukan
                    'issued' => '2026-05-29T10:05:00+07:00', // Waktu saat data ini dicatat dan dirilis ke sistem
                    'performer' => [
                        [
                            'reference' => 'Practitioner/{practitioner_id}', // ID Tenaga Kesehatan / Perawat / Dokter pemeriksa
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
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ],
                    'effectiveDateTime' => '2026-05-29T10:00:00+07:00',
                    'issued' => '2026-05-29T10:10:00+07:00',
                    'performer' => [
                        [
                            'reference' => 'Practitioner/{practitioner_id}',
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
    ];