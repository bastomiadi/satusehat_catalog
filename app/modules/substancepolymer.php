<?php
    // =====================================================
    // SUBSTANCE POLYMER
    // =====================================================

    return [
        'label' => 'SubstancePolymer',
        'icon' => '🧬',
        'description' => 'Substance polymers',
        'endpoints' => [
            [
                'key' => 'get_subpolymer',
                'label' => 'Get SubstancePolymer',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstancePolymer',
                'description' => 'Get substance polymers',
                'params' => [],
            ],
        ]
    ];