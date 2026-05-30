<?php    
    // =====================================================
    // VERIFICATION RESULT
    // =====================================================

    return [
        'label' => 'VerificationResult',
        'icon' => '✅',
        'description' => 'Verification results',
        'endpoints' => [
            [
                'key' => 'get_verify',
                'label' => 'Get VerificationResult',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/VerificationResult',
                'description' => 'Get verification results',
                'params' => [],
            ],
        ]
    ];