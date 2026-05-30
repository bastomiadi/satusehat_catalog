<?php    
    // =====================================================
    // LINKAGE
    // =====================================================

    return [
        'label' => 'Linkage',
        'icon' => '🔗',
        'description' => 'Linkages and associations',
        'endpoints' => [
            [
                'key' => 'get_linkage',
                'label' => 'Get Linkage',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Linkage',
                'description' => 'Get linkages',
                'params' => [],
            ],
        ]
    ];