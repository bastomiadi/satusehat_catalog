<?php    
    // =====================================================
    // SUPPLY REQUEST
    // =====================================================

    return [
        'label' => 'SupplyRequest',
        'icon' => '📦',
        'description' => 'Supply requests',
        'endpoints' => [
            [
                'key' => 'create_supplyrequest',
                'label' => 'Create SupplyRequest',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/SupplyRequest',
                'description' => 'Create supply request',
                'params' => [],
                'body' => [
                    'resourceType' => 'SupplyRequest',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];