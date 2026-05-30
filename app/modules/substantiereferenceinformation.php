<?php    
    // =====================================================
    // SUBSTANCE REFERENCE INFORMATION
    // =====================================================

    return [
        'label' => 'SubstanceReferenceInformation',
        'icon' => '🧬',
        'description' => 'Substance reference information',
        'endpoints' => [
            [
                'key' => 'get_subrefinfo',
                'label' => 'Get SubstanceReferenceInformation',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceReferenceInformation',
                'description' => 'Get substance reference information',
                'params' => [],
            ],
        ]
    ];