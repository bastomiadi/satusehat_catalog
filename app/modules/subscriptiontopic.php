<?php
    // =====================================================
    // SUBSCRIPTION TOPIC
    // =====================================================

    return [
        'label' => 'SubscriptionTopic',
        'icon' => '🔔',
        'description' => 'Subscription topics',
        'endpoints' => [
            [
                'key' => 'get_subtopic',
                'label' => 'Get SubscriptionTopic',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SubscriptionTopic',
                'description' => 'Get subscription topics',
                'params' => [],
            ],
        ]
    ];