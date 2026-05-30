<?php
    // =====================================================
    // PROVENANCE
    // =====================================================

    return [
        'label' => 'Provenance',
        'icon' => '🕓',
        'description' => 'Audit trail',
        'endpoints' => [

            [
                'key' => 'create_provenance',
                'label' => 'Create Provenance',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Provenance',
                'description' => 'Create provenance',
                'params' => [],
                'body' => [
                    'resourceType' => 'Provenance',

                    'recorded' => date('c'),

                    'target' => [
                        [
                            'reference' => 'Patient/{patient_id}'
                        ]
                    ]
                ]
            ],

            [
                'key' => 'get_provenance',
                'label' => 'Get Provenance by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Provenance/{id}',
                'description' => 'Get provenance by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Provenance ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_provenance',
                'label' => 'Search Provenance by Target',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Provenance?target={target_id}',
                'description' => 'Search provenance by target',
                'params' => [
                    [
                        'name' => 'target_id',
                        'type' => 'text',
                        'placeholder' => 'Target ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'update_provenance',
                'label' => 'Update Provenance',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Provenance/{id}',
                'description' => 'Update provenance',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Provenance ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'delete_provenance',
                'label' => 'Delete Provenance',
                'method' => 'DELETE',
                'path' => '/fhir-r4/v1/Provenance/{id}',
                'description' => 'Delete provenance',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Provenance ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'history_provenance',
                'label' => 'History Provenance',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Provenance/{id}/_history',
                'description' => 'Get provenance history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Provenance ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_provenance',
            //     'label' => 'History Type Provenance',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Provenance/_history',
            //     'description' => 'Get all provenance history',
            //     'params' => [],
            // ],
        ]
    ];