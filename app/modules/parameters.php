<?php
    // =====================================================
    // PARAMETERS
    // =====================================================

    return [
        'label' => 'Parameters',
        'icon' => '⚙️',
        'description' => 'Parameters and inputs',
        'endpoints' => [
            [
                'key' => 'get_parameters',
                'label' => 'Get Parameters',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Parameters',
                'description' => 'Get parameters',
                'params' => [],
            ],
        ]
    ];