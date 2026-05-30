<?php 
    // =====================================================
    // IMAGING STUDY
    // =====================================================

    return [
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
                        'reference' => 'Patient/{patient_id}', // Ganti dengan ID Pasien riil SATUSEHAT
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
                            'system' => 'http://sys-ids.kemkes.go.id/acsn/{patient_id}', // Menggunakan ID Pasien di dalam URL system
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
    ];