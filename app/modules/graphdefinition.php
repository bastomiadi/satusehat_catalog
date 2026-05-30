<?php    
    // =====================================================
    // GRAPH DEFINITION
    // =====================================================

    return [
        'label' => 'GraphDefinition',
        'icon' => '🌐',
        'description' => 'Graph definitions',
        'endpoints' => [
            [
                'key' => 'get_graphdef',
                'label' => 'Get GraphDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/GraphDefinition',
                'description' => 'Get graph definitions',
                'params' => [],
            ],
        ]
    ];