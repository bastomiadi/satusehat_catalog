<?php    
    // =====================================================
    // CLAIM
    // =====================================================

    return [
        'label' => 'Claim',
        'icon' => '💰',
        'description' => 'Insurance claims',
        'endpoints' => [
            [
                'key' => 'create_claim',
                'label' => 'Create Claim',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Claim',
                'description' => 'Create insurance claim',
                'params' => [],
                'body' => [
                    'resourceType' => 'Claim',
                    'type' => ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/claim-type', 'code' => '']]],
                    'patient' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];