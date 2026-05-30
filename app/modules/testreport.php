<?php    
    // =====================================================
    // TEST REPORT
    // =====================================================

    return [
        'label' => 'TestReport',
        'icon' => '🧪',
        'description' => 'Test reports',
        'endpoints' => [
            [
                'key' => 'get_testreport',
                'label' => 'Get TestReport',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/TestReport',
                'description' => 'Get test reports',
                'params' => [],
            ],
        ]
    ];