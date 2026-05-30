<?php
    // =====================================================
    // SERVICE REQUEST
    // =====================================================

    return [
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
                            'system' => 'http://sys-ids.kemkes.go.id/servicerequest/{org_id}', // Menggunakan Kode Fasyankes Anda (Org ID)
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
                        'reference' => 'Patient/{patient_id}', // ID Pasien riil SATUSEHAT
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}' // Mengikat ID Encounter kunjungan terkait
                    ],
                    'occurrenceDateTime' => '2026-05-29T10:15:00+07:00', // Jadwal rencana pelaksanaan pemeriksaan dilakukan
                    'authoredOn' => '2026-05-29T10:00:00+07:00', // Waktu pembuatan order/instruksi oleh dokter pelapor
                    'requester' => [
                        'reference' => 'Practitioner/{practitioner_id}', // ID Dokter DPJP yang meminta pemeriksaan
                        'display' => 'Nama Dokter DPJP Pengirim'
                    ],
                    'performer' => [
                        [
                            'reference' => 'Organization/{organization_id}', // Fasyankes/Laboratorium penanggung jawab pelaksana tindakan
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
                            'system' => 'http://sys-ids.kemkes.go.id/servicerequest/{servicerequest_id}',
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
                        'reference' => 'Patient/{patient_id}',
                        'display' => 'Nama Pasien Sesuai KTP'
                    ],
                    'encounter' => [
                        'reference' => 'Encounter/{encounter_id}'
                    ],
                    'occurrenceDateTime' => '2026-05-29T10:15:00+07:00',
                    'authoredOn' => '2026-05-29T10:00:00+07:00',
                    'requester' => [
                        'reference' => 'Practitioner/{practitioner_id}',
                        'display' => 'Nama Dokter DPJP Pengirim'
                    ],
                    'performer' => [
                        [
                            'reference' => 'Organization/{organization_id}',
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
    ];