<?php    
    // =====================================================
    // LIBRARY
    // =====================================================

    return [
        'label' => 'Library',
        'icon' => '📚',
        'description' => 'Libraries and knowledge',
        'endpoints' => [
            [
                'key' => 'get_library',
                'label' => 'Get Library',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Library',
                'description' => 'Get libraries',
                'params' => [],
            ],
        ]
    ];