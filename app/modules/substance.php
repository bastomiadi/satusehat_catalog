<?php
    // =====================================================
    // SUBSTANCE
    // =====================================================

    return [
        'label' => 'Substance',
        'icon' => '🧪',
        'description' => 'Substances and chemicals',
        'endpoints' => [
            [
                'key' => 'get_substance',
                'label' => 'Get Substance',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Substance',
                'description' => 'Get substances',
                'params' => [],
            ],
        ]
];