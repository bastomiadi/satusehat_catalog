<?php    
    // =====================================================
    // SUBSCRIPTION
    // =====================================================

    return [
        'label' => 'Subscription',
        'icon' => '🔔',
        'description' => 'Subscriptions and notifications',
        'endpoints' => [
            [
                'key' => 'create_subscription',
                'label' => 'Create Subscription',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Subscription',
                'description' => 'Create subscription',
                'params' => [],
                'body' => [
                    'resourceType' => 'Subscription',
                    'status' => 'active',
                    'reason' => 'Monitoring'
                ]
            ],
        ]
    ];