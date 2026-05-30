<?php    
    // =====================================================
    // GOAL
    // =====================================================

    return [
        'label' => 'Goal',
        'icon' => '🎯',
        'description' => 'Health goals and objectives',
        'endpoints' => [
            [
                'key' => 'create_goal',
                'label' => 'Create Goal',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Goal',
                'description' => 'Create health goal',
                'params' => [],
                'body' => [
                    'resourceType' => 'Goal',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];