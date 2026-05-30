<?php
    // =====================================================
    // CLINICAL IMPRESSION
    // =====================================================

    return [
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
    ];