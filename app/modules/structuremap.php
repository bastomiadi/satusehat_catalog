<?php
    // =====================================================
    // STRUCTURE MAP
    // =====================================================

    return [
        'label' => 'StructureMap',
        'icon' => '🗺️',
        'description' => 'Structure maps',
        'endpoints' => [
            [
                'key' => 'get_structuremap',
                'label' => 'Get StructureMap',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/StructureMap',
                'description' => 'Get structure maps',
                'params' => [],
            ],
        ]
    ];