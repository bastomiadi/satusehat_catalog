<?php    
    // =====================================================
    // MOLECULAR SEQUENCE
    // =====================================================

    return [
        'label' => 'MolecularSequence',
        'icon' => '🧬',
        'description' => 'Molecular sequences',
        'endpoints' => [
            [
                'key' => 'get_molecularseq',
                'label' => 'Get MolecularSequence',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MolecularSequence',
                'description' => 'Get molecular sequences',
                'params' => [],
            ],
        ]
    ];