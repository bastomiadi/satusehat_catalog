<?php    
    // =====================================================
    // COMMUNICATION REQUEST
    // =====================================================

    return [
        'label' => 'CommunicationRequest',
        'icon' => '💬',
        'description' => 'Communication requests',
        'endpoints' => [
            [
                'key' => 'create_communicationrequest',
                'label' => 'Create CommunicationRequest',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/CommunicationRequest',
                'description' => 'Create communication request',
                'params' => [],
                'body' => [
                    'resourceType' => 'CommunicationRequest',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];