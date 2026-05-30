<?php
    // =====================================================
    // NAMING SYSTEM
    // =====================================================

    return [
        'label' => 'NamingSystem',
        'icon' => '📛',
        'description' => 'Naming systems',
        'endpoints' => [
            [
                'key' => 'get_namingsystem',
                'label' => 'Get NamingSystem',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/NamingSystem',
                'description' => 'Get naming systems',
                'params' => [],
            ],
        ]
    ];