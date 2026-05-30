<?php
    // =====================================================
    // RESEARCH SUBJECT
    // =====================================================

    return [
        'label' => 'ResearchSubject',
        'icon' => '👨‍🔬',
        'description' => 'Research subjects',
        'endpoints' => [
            [
                'key' => 'get_researchsubject',
                'label' => 'Get ResearchSubject',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ResearchSubject',
                'description' => 'Get research subjects',
                'params' => [],
            ],
        ]
    ];