<?php    
    // =====================================================
    // CLAIM RESPONSE
    // =====================================================

    return [
        'label' => 'ClaimResponse',
        'icon' => '💰',
        'description' => 'Claim responses and adjudications',
        'endpoints' => [
            [
                'key' => 'get_claimresponse',
                'label' => 'Get ClaimResponse',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ClaimResponse/{id}',
                'description' => 'Get claim response',
                'params' => [['name' => 'id', 'type' => 'text', 'placeholder' => 'ClaimResponse ID', 'default' => '']],
            ],
        ]
    ];