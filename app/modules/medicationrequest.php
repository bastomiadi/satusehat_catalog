<?php 
    // =====================================================
    // MEDICATION REQUEST
    // =====================================================

    return [
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
                        'reference' => 'Medication/{medication_id}', // Merujuk ke ID resource Medication yang sudah di-POST sebelumnya
                        'display' => 'Amoxicillin 500 mg Kaplet'
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}' // Mengikat ID Encounter kunjungan terkait
                    ],
                    'authoredOn' => '2026-05-29T10:00:00+07:00', // Waktu penulisan resep oleh dokter
                    'requester' => [
                        'reference' => 'Practitioner/{practitioner_id}', // ID Dokter DPJP penanggung jawab yang menulis resep
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
                        'reference' => 'Medication/{medication_id}',
                        'display' => 'Amoxicillin 500 mg Kaplet'
                    ],
                    'subject' => [
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ],
                    'authoredOn' => '2026-05-29T10:00:00+07:00',
                    'requester' => [
                        'reference' => 'Practitioner/{practitioner_id}',
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
    ];