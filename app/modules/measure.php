<?php    
    // =====================================================
    // MEASURE
    // =====================================================

    return [
        'label' => 'Measure',
        'icon' => '📊',
        'description' => 'Measures and metrics',
        'endpoints' => [
            [
                'key' => 'get_measure',
                'label' => 'Get Measure',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Measure',
                'description' => 'Get measures',
                'params' => [],
            ],
        ]
    ];