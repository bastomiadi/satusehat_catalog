<?php
    // =====================================================
    // SUBSCRIPTION STATUS
    // =====================================================

    return [
        'label' => 'SubscriptionStatus',
        'icon' => '🔔',
        'description' => 'Subscription statuses',
        'endpoints' => [
            [
                'key' => 'get_substatus',
                'label' => 'Get SubscriptionStatus',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubscriptionStatus',
                'description' => 'Get subscription statuses',
                'params' => [],
            ],
        ]
    ];