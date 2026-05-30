<?php    
    // =====================================================
    // SLOT
    // =====================================================

    return [
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
    ];