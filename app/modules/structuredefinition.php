<?php    
    // =====================================================
    // STRUCTURE DEFINITION
    // =====================================================

    return [
        'label' => 'StructureDefinition',
        'icon' => '🏗️',
        'description' => 'Structure definitions',
        'endpoints' => [
            [
                'key' => 'get_structuredef',
                'label' => 'Get StructureDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/StructureDefinition',
                'description' => 'Get structure definitions',
                'params' => [],
            ],
        ]
    ];