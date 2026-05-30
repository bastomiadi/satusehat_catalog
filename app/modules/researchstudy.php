<?php
    // =====================================================
    // RESEARCH STUDY
    // =====================================================

    return [
        'label' => 'ResearchStudy',
        'icon' => '🔬',
        'description' => 'Research studies',
        'endpoints' => [
            [
                'key' => 'get_researchstudy',
                'label' => 'Get ResearchStudy',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ResearchStudy',
                'description' => 'Get research studies',
                'params' => [],
            ],
        ]
    ];