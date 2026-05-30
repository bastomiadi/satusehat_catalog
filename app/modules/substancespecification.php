<?php
    // =====================================================
    // SUBSTANCE SPECIFICATION
    // =====================================================

    return [
        'label' => 'SubstanceSpecification',
        'icon' => '🧪',
        'description' => 'Substance specifications',
        'endpoints' => [
            [
                'key' => 'get_subspec',
                'label' => 'Get SubstanceSpecification',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceSpecification',
                'description' => 'Get substance specifications',
                'params' => [],
            ],
        ]
    ];