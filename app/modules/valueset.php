<?php
    // =====================================================
    // VALUE SET
    // =====================================================

    return [
        'label' => 'ValueSet',
        'icon' => '📋',
        'description' => 'Value sets and codings',
        'endpoints' => [
            [
                'key' => 'get_valueset',
                'label' => 'Get ValueSet',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ValueSet',
                'description' => 'Get value sets',
                'params' => [],
            ],
        ]
    ];