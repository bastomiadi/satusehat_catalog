<?php
    // =====================================================
    // SUBSTANCE PROTEIN
    // =====================================================

    return [
        'label' => 'SubstanceProtein',
        'icon' => '🧬',
        'description' => 'Substance proteins',
        'endpoints' => [
            [
                'key' => 'get_subprotein',
                'label' => 'Get SubstanceProtein',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceProtein',
                'description' => 'Get substance proteins',
                'params' => [],
            ],
        ]
    ];