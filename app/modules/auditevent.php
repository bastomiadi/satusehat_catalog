<?php    
    // =====================================================
    // AUDIT EVENT
    // =====================================================

    return [
        'label' => 'AuditEvent',
        'icon' => '📝',
        'description' => 'Audit events and logs',
        'endpoints' => [
            [
                'key' => 'create_auditevent',
                'label' => 'Create AuditEvent',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/AuditEvent',
                'description' => 'Create audit event',
                'params' => [],
                'body' => [
                    'resourceType' => 'AuditEvent',
                    'type' => ['coding' => [['system' => '', 'code' => '', 'display' => '']]]
                ]
            ],
        ]
    ];