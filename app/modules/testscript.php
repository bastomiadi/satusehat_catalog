<?php    
    // =====================================================
    // TEST SCRIPT
    // =====================================================

    return [
        'label' => 'TestScript',
        'icon' => '🧪',
        'description' => 'Test scripts',
        'endpoints' => [
            [
                'key' => 'get_testscript',
                'label' => 'Get TestScript',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/TestScript',
                'description' => 'Get test scripts',
                'params' => [],
            ],
        ]
    ];