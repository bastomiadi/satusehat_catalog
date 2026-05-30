<?php    
    // =====================================================
    // MEASURE REPORT
    // =====================================================

    return [
        'label' => 'MeasureReport',
        'icon' => '📊',
        'description' => 'Measure reports',
        'endpoints' => [
            [
                'key' => 'get_measurereport',
                'label' => 'Get MeasureReport',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MeasureReport',
                'description' => 'Get measure reports',
                'params' => [],
            ],
        ]
    ];