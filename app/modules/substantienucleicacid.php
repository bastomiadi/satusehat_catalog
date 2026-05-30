<?php    
    // =====================================================
    // SUBSTANCE NUCLEIC ACID
    // =====================================================

    return [
        'label' => 'SubstanceNucleicAcid',
        'icon' => '🧬',
        'description' => 'Substance nucleic acids',
        'endpoints' => [
            [
                'key' => 'get_subnucleic',
                'label' => 'Get SubstanceNucleicAcid',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubstanceNucleicAcid',
                'description' => 'Get substance nucleic acids',
                'params' => [],
            ],
        ]
    ];