<?php
    // =====================================================
    // OBSERVATION DEFINITION
    // =====================================================

    return [
        'label' => 'ObservationDefinition',
        'icon' => '📊',
        'description' => 'Observation definitions',
        'endpoints' => [
            [
                'key' => 'get_obsdef',
                'label' => 'Get ObservationDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ObservationDefinition',
                'description' => 'Get observation definitions',
                'params' => [],
            ],
        ]
    ];