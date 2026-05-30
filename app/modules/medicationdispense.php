<?php
    // =====================================================
    // MEDICATION DISPENSE
    // =====================================================

    return [
        'label' => 'MedicationDispense',
        'icon' => '📦',
        'description' => 'Medication dispensing records and fulfillment',
        'endpoints' => [
            [
                'key' => 'search_medicationdispense_by_patient_and_encounter',
                'label' => 'Search MedicationDispense by Patient & Encounter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationDispense?subject={patient_id}&context={encounter_id}',
                'description' => 'Mencari data penyerahan/pemberian obat (MedicationDispense) berdasarkan ID Pasien (subject) dan ID Kunjungan (context). Kedua parameter ini WAJIB ada bersamaan.',
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
                'key' => 'search_medicationdispense_by_patient_and_prescription',
                'label' => 'Search MedicationDispense by Patient & Prescription',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationDispense?subject={patient_id}&prescription={prescription_id}',
                'description' => 'Mencari data penyerahan/pemberian obat berdasarkan ID Pasien (subject) dan ID Resep/MedicationRequest (prescription). Kedua parameter ini WAJIB ada bersamaan.',
                'params' => [
                    [
                        'name' => 'patient_id',
                        'type' => 'text',
                        'placeholder' => 'SATUSEHAT Patient ID (cth: 100000000001)',
                        'default' => ''
                    ],
                    [
                        'name' => 'prescription_id',
                        'type' => 'text',
                        'placeholder' => 'Format: UUID (cth: cf92db3e-a044-4e15-83fb-b7ec3a30ba76)',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'create_medicationdispense',
                'label' => 'Create MedicationDispense',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/MedicationDispense',
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
                'path' => '/fhir-r4/v1/MedicationDispense/{id}',
                'description' => 'Mengambil detail data realisasi penyerahan obat.',
                'params' => [
                    ['name' => 'id', 'type' => 'text', 'placeholder' => 'MedicationDispense ID', 'default' => '']
                ]
            ],
            // [
            //     'key' => 'create_medicationdispense',
            //     'label' => 'Create MedicationDispense',
            //     'method' => 'POST',
            //     'path' => '/fhir-r4/v1/MedicationDispense',
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
            //     'path' => '/fhir-r4/v1/MedicationDispense/{id}',
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
                'path' => '/fhir-r4/v1/MedicationDispense?subject={patient_id}',
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
                'path' => '/fhir-r4/v1/MedicationDispense/{id}',
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
            //     'path' => '/fhir-r4/v1/MedicationDispense/{id}',
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
                'path' => '/fhir-r4/v1/MedicationDispense/{id}/_history',
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
                'path' => '/fhir-r4/v1/MedicationDispense/{id}',
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
            //     'path' => '/fhir-r4/v1/MedicationDispense/_history',
            //     'description' => 'Get all medication dispense history',
            //     'params' => [],
            // ],
        ]
    ];