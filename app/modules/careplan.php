<?php
    // =====================================================
    // CARE PLAN
    // =====================================================

    return [
        'label' => 'CarePlan',
        'icon' => '📝',
        'description' => 'Care plans',
        'endpoints' => [

            [
                'key' => 'create_careplan',
                'label' => 'Create CarePlan',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/CarePlan',
                'description' => 'Create care plan',
                'params' => [],
                'body' => [
                    'resourceType' => 'CarePlan',
                    'status' => 'active',
                    'intent' => 'plan',

                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
            ],

            [
                'key' => 'get_careplan',
                'label' => 'Get CarePlan by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CarePlan/{id}',
                'description' => 'Get care plan by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CarePlan ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_careplan',
                'label' => 'Search CarePlan by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CarePlan?subject={patient_id}',
                'description' => 'Search care plans for patient',
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
                'key' => 'update_careplan',
                'label' => 'Update CarePlan',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/CarePlan/{id}',
                'description' => 'Fungsi dari ReST API ini adalah untuk melakukan perubahan data (update) terkait resource CarePlan secara keseluruhan.',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CarePlan ID',
                        'default' => ''
                    ]
                ],
                'body' => [
                    'resourceType' => 'CarePlan'
                ]
            ],
            
            [
                'key' => 'patch_careplan',
                'label' => 'Patch CarePlan',
                'method' => 'PATCH',
                'path' => '/fhir-r4/v1/CarePlan/{id}',
                'description' => 'Fungsi dari ReST API ini adalah untuk melakukan perubahan sebagian dari data terkait resource CarePlan (patching).',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CarePlan ID',
                        'default' => ''
                    ]
                ],
                'body' => [
                    [
                        'op' => 'replace',
                        'path' => '/language',
                        'value' => 'id'
                    ]
                ]
            ],

            // [
            //     'key' => 'delete_careplan',
            //     'label' => 'Delete CarePlan',
            //     'method' => 'DELETE',
            //     'path' => '/fhir-r4/v1/CarePlan/{id}',
            //     'description' => 'Delete care plan',
            //     'params' => [
            //         [
            //             'name' => 'id',
            //             'type' => 'text',
            //             'placeholder' => 'CarePlan ID',
            //             'default' => ''
            //         ]
            //     ],
            // ],

            [
                'key' => 'history_careplan',
                'label' => 'History CarePlan',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CarePlan/{id}/_history',
                'description' => 'Get care plan history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CarePlan ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_careplan',
            //     'label' => 'History Type CarePlan',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/CarePlan/_history',
            //     'description' => 'Get all care plan history',
            //     'params' => [],
            // ],
        ]
    ];