<?php 
    // =====================================================
    // CONTRACT
    // =====================================================

    return [
        'label' => 'Contract',
        'icon' => '📝',
        'description' => 'Contracts and agreements',
        'endpoints' => [
            [
                'key' => 'create_contract',
                'label' => 'Create Contract',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Contract',
                'description' => 'Create contract',
                'params' => [],
                'body' => [
                    'resourceType' => 'Contract',
                    'status' => 'active'
                ]
            ],
        ]
    ];