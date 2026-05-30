<?php
    // =====================================================
    // PLAN DEFINITION
    // =====================================================

    return [
        'label' => 'PlanDefinition',
        'icon' => '📋',
        'description' => 'Plan definitions',
        'endpoints' => [
            [
                'key' => 'get_plandef',
                'label' => 'Get PlanDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/PlanDefinition',
                'description' => 'Get plan definitions',
                'params' => [],
            ],
        ]
    ];