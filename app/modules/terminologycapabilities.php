<?php    
    // =====================================================
    // TERMINOLOGY CAPABILITIES
    // =====================================================

    return [
        'label' => 'TerminologyCapabilities',
        'icon' => '📚',
        'description' => 'Terminology capabilities',
        'endpoints' => [
            [
                'key' => 'get_terminologycap',
                'label' => 'Get TerminologyCapabilities',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/TerminologyCapabilities',
                'description' => 'Get terminology capabilities',
                'params' => [],
            ],
        ]
    ];