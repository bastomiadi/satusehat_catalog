<?php
    // =====================================================
    // COVERAGE
    // =====================================================

    return [
        'label' => 'Coverage',
        'icon' => '🛡️',
        'description' => 'Insurance coverage',
        'endpoints' => [

            [
                'key' => 'create_coverage',
                'label' => 'Create Coverage',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Coverage',
                'description' => 'Create coverage',
                'params' => [],
                'body' => [
                    'resourceType' => 'Coverage',
                    'status' => 'active',

                    'beneficiary' => [
                        'reference' => 'Patient/{patient_id}'
                    ],

                    'subscriberId' => ''
                ]
            ],

            [
                'key' => 'get_coverage',
                'label' => 'Get Coverage by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Coverage/{id}',
                'description' => 'Get coverage by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Coverage ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_coverage',
                'label' => 'Search Coverage by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Coverage?beneficiary={patient_id}',
                'description' => 'Search coverage for patient',
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
                'key' => 'update_coverage',
                'label' => 'Update Coverage',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Coverage/{id}',
                'description' => 'Update coverage',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Coverage ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'delete_coverage',
                'label' => 'Delete Coverage',
                'method' => 'DELETE',
                'path' => '/fhir-r4/v1/Coverage/{id}',
                'description' => 'Delete coverage',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Coverage ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'history_coverage',
                'label' => 'History Coverage',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Coverage/{id}/_history',
                'description' => 'Get coverage history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Coverage ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_coverage',
            //     'label' => 'History Type Coverage',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Coverage/_history',
            //     'description' => 'Get all coverage history',
            //     'params' => [],
            // ],
        ]
    ];