<?php
    // =====================================================
    // RESEARCH ELEMENT DEFINITION
    // =====================================================

    return [
        'label' => 'ResearchElementDefinition',
        'icon' => '🔬',
        'description' => 'Research element definitions',
        'endpoints' => [
            [
                'key' => 'get_researchelem',
                'label' => 'Get ResearchElementDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ResearchElementDefinition',
                'description' => 'Get research element definitions',
                'params' => [],
            ],
        ]
    ];