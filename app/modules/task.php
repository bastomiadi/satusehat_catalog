<?php    
    // =====================================================
    // TASK
    // =====================================================

    return [
        'label' => 'Task',
        'icon' => '📋',
        'description' => 'Tasks and workflow management',
        'endpoints' => [
            [
                'key' => 'create_task',
                'label' => 'Create Task',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Task',
                'description' => 'Create task',
                'params' => [],
                'body' => [
                    'resourceType' => 'Task',
                    'status' => 'requested',
                    'intent' => 'order'
                ]
            ],
        ]
    ];