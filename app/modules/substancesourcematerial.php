<?php
    // =====================================================
    // SUBSTANCE SOURCE MATERIAL
    // =====================================================

    return [
        'label' => 'SubstanceSourceMaterial',
        'icon' => '🧬',
        'description' => 'Substance source materials',
        'endpoints' => [
            [
                'key' => 'get_subsource',
                'label' => 'Get SubstanceSourceMaterial',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceSourceMaterial',
                'description' => 'Get substance source materials',
                'params' => [],
            ],
        ]
    ];