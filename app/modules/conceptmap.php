<?php
    // =====================================================
    // CONCEPT MAP
    // =====================================================

    return [
        'label' => 'ConceptMap',
        'icon' => '🗺️',
        'description' => 'Concept maps and translations',
        'endpoints' => [
            [
                'key' => 'get_conceptmap',
                'label' => 'Get ConceptMap',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ConceptMap',
                'description' => 'Get concept maps',
                'params' => [],
            ],
        ]
    ];