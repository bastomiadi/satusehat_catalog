<?php    
    // =====================================================
    // MESSAGE DEFINITION
    // =====================================================

    return [
        'label' => 'MessageDefinition',
        'icon' => '📧',
        'description' => 'Message definitions',
        'endpoints' => [
            [
                'key' => 'get_messagedef',
                'label' => 'Get MessageDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MessageDefinition',
                'description' => 'Get message definitions',
                'params' => [],
            ],
        ]
    ];