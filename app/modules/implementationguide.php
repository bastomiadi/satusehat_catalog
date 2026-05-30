<?php    
    // =====================================================
    // IMPLEMENTATION GUIDE
    // =====================================================

    return [
        'label' => 'ImplementationGuide',
        'icon' => '📖',
        'description' => 'Implementation guides',
        'endpoints' => [
            [
                'key' => 'get_ig',
                'label' => 'Get ImplementationGuide',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ImplementationGuide',
                'description' => 'Get implementation guides',
                'params' => [],
            ],
        ]
    ];