<?php
    // =====================================================
    // DIAGNOSTIC REPORT
    // =====================================================

    return [
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
    ];