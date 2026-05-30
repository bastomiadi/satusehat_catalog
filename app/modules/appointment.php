<?php
    // =====================================================
    // APPOINTMENT
    // =====================================================

    return [
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
                            'reference' => 'Slot/{slot_id}' // Menandai blok celah waktu Slot yang di-booking
                        ]
                    ],
                    'start' => '2026-06-01T09:00:00+07:00', // Estimasi waktu mulai janji temu
                    'end' => '2026-06-01T09:15:00+07:00',   // Estimasi waktu selesai janji temu
                    'created' => '2026-05-29T18:00:00+07:00', // Waktu booking ini dibuat oleh sistem rumah sakit
                    'comment' => 'Pendaftaran antrean pemeriksaan rutin anak via Mobile JKN / Aplikasi SIMRS',
                    'participant' => [
                        [
                            'actor' => [
                                'reference' => 'Patient/{patient_id}', // Referensi pasien yang berobat
                                'display' => 'Nama Pasien Sesuai KTP'
                            ],
                            'status' => 'accepted', // Status partisipasi pasien
                            'required' => 'required'
                        ],
                        [
                            'actor' => [
                                'reference' => 'Practitioner/{practitioner_id}', // Referensi dokter pemeriksa
                                'display' => 'Nama Dokter Spesialis Anak'
                            ],
                            'status' => 'accepted',
                            'required' => 'required'
                        ],
                        [
                            'actor' => [
                                'reference' => 'Location/{location_id}', // Ruangan Poliklinik tujuan
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
                            'reference' => 'Slot/{slot_id}'
                        ]
                    ],
                    'start' => '2026-06-01T09:00:00+07:00',
                    'end' => '2026-06-01T09:15:00+07:00',
                    'created' => '2026-05-29T18:00:00+07:00',
                    'comment' => 'Pelayanan Konsultasi Selesai Dilakukan',
                    'participant' => [
                        [
                            'actor' => [
                                'reference' => 'Patient/{patient_id}',
                                'display' => 'Nama Pasien Sesuai KTP'
                            ],
                            'status' => 'accepted',
                            'required' => 'required'
                        ],
                        [
                            'actor' => [
                                'reference' => 'Practitioner/{practitioner_id}',
                                'display' => 'Nama Dokter Spesialis Anak'
                            ],
                            'status' => 'accepted',
                            'required' => 'required'
                        ],
                        [
                            'actor' => [
                                'reference' => 'Location/{location_id}',
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
    ];