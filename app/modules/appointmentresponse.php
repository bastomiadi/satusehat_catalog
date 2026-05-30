<?php
    // =====================================================
    // APPOINTMENT RESPONSE
    // =====================================================

    return [
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
    ];