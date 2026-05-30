<?php
    // =====================================================
    // RESEARCH DEFINITION
    // =====================================================

    return [
        'label' => 'ResearchDefinition',
        'icon' => '🔬',
        'description' => 'Research definitions',
        'endpoints' => [
            [
                'key' => 'get_researchdef',
                'label' => 'Get ResearchDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ResearchDefinition',
                'description' => 'Get research definitions',
                'params' => [],
            ],
        ]
    ];