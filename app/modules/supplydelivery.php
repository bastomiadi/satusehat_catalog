<?php    
    // =====================================================
    // SUPPLY DELIVERY
    // =====================================================

    return [
        'label' => 'SupplyDelivery',
        'icon' => '📦',
        'description' => 'Supply deliveries',
        'endpoints' => [
            [
                'key' => 'create_supplydelivery',
                'label' => 'Create SupplyDelivery',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/SupplyDelivery',
                'description' => 'Create supply delivery',
                'params' => [],
                'body' => [
                    'resourceType' => 'SupplyDelivery',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];