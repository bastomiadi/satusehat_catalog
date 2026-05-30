<?php
    // =====================================================
    // MEDICATION STATEMENT
    // =====================================================

    return [
        'label' => 'MedicationStatement',
        'icon' => '📝',
        'description' => 'Medication statements and patient medication history',
        'endpoints' => [
            [
                'key' => 'create_medicationstatement',
                'label' => 'Create MedicationStatement',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/MedicationStatement',
                'description' => 'Create medication statement',
                'params' => [],
                'body' => [
                    'resourceType' => 'MedicationStatement',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
            [
                'key' => 'get_medicationstatement',
                'label' => 'Get MedicationStatement by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationStatement/{id}',
                'description' => 'Get medication statement by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationStatement ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'search_medicationstatement',
                'label' => 'Search MedicationStatement by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationStatement?subject={patient_id}',
                'description' => 'Search medication statements for a patient',
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
                'key' => 'update_medicationstatement',
                'label' => 'Update MedicationStatement',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/MedicationStatement/{id}',
                'description' => 'Update medication statement',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationStatement ID',
                        'default' => ''
                    ]
                ],
            ],
            // [
            //     'key' => 'delete_medicationstatement',
            //     'label' => 'Delete MedicationStatement',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/MedicationStatement/{id}',
            //     'description' => 'Delete medication statement',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'MedicationStatement ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],
            [
                'key' => 'history_medicationstatement',
                'label' => 'History MedicationStatement',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationStatement/{id}/_history',
                'description' => 'Get medication statement history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationStatement ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'patch_medicationstatement',
                'label' => 'Patch MedicationStatement',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/MedicationStatement/{id}',
                'description' => 'Patch medication statement',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'MedicationStatement ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_medicationstatement',
            //     'label' => 'History Type MedicationStatement',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/MedicationStatement/_history',
            //     'description' => 'Get all medication statement history',
            //     'params' => [],
            // ],
        ]
    ];