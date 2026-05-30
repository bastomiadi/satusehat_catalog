<?php
    // =====================================================
    // ENDPOINT
    // =====================================================

    return [
        'label' => 'Endpoint',
        'icon' => '🔗',
        'description' => 'API endpoints and connections',
        'endpoints' => [
            [
                'key' => 'get_endpoint',
                'label' => 'Get Endpoint',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Endpoint',
                'description' => 'Get endpoints',
                'params' => [],
            ],
        ]
    ];