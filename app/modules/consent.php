<?php
    // =====================================================
    // CONSENT
    // =====================================================

    return [
        'label' => 'Consent',
        'icon' => '✅',
        'description' => 'Patient consent',
        'endpoints' => [

            [
                'key' => 'create_consent',
                'label' => 'Create Consent',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Consent',
                'description' => 'Create consent',
                'params' => [],
                'body' => [
                    'resourceType' => 'Consent',
                    'status' => 'active',

                    'patient' => [
                        'reference' => 'Patient/{patient_id}'
                    ]
                ]
            ],

            [
                'key' => 'get_consent',
                'label' => 'Get Consent by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Consent/{id}',
                'description' => 'Get consent by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Consent ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'search_consent',
                'label' => 'Search Consent by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Consent?patient={patient_id}',
                'description' => 'Search consents for patient',
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
                'key' => 'update_consent',
                'label' => 'Update Consent',
                'method' => 'PUT',
                'path' => '/fhir-r4/v1/Consent/{id}',
                'description' => 'Update consent',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Consent ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'delete_consent',
                'label' => 'Delete Consent',
                'method' => 'DELETE',
                'path' => '/fhir-r4/v1/Consent/{id}',
                'description' => 'Delete consent',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Consent ID',
                        'default' => ''
                    ]
                ],
            ],

            [
                'key' => 'history_consent',
                'label' => 'History Consent',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Consent/{id}/_history',
                'description' => 'Get consent history',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'Consent ID',
                        'default' => ''
                    ]
                ],
            ],

            // [
            //     'key' => 'history_type_consent',
            //     'label' => 'History Type Consent',
            //     'method' => 'GET',
            //     'path' => '/fhir-r4/v1/Consent/_history',
            //     'description' => 'Get all consent history',
            //     'params' => [],
            // ],
        ]
    ];