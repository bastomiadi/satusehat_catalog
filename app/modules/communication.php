<?php    
    // =====================================================
    // COMMUNICATION
    // =====================================================

    return [
        'label' => 'Communication',
        'icon' => '💬',
        'description' => 'Communications and messages',
        'endpoints' => [
            [
                'key' => 'create_communication',
                'label' => 'Create Communication',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Communication',
                'description' => 'Create communication',
                'params' => [],
                'body' => [
                    'resourceType' => 'Communication',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];