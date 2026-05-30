<?php    
    // =====================================================
    // COMPARTMENT DEFINITION
    // =====================================================

    return [
        'label' => 'CompartmentDefinition',
        'icon' => '📦',
        'description' => 'Compartment definitions',
        'endpoints' => [
            [
                'key' => 'get_compartment',
                'label' => 'Get CompartmentDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CompartmentDefinition',
                'description' => 'Get compartment definitions',
                'params' => [],
            ],
        ]
    ];