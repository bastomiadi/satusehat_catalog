<?php    
    // =====================================================
    // MESSAGE HEADER
    // =====================================================

    return [
        'label' => 'MessageHeader',
        'icon' => '📧',
        'description' => 'Message headers',
        'endpoints' => [
            [
                'key' => 'get_messageheader',
                'label' => 'Get MessageHeader',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MessageHeader',
                'description' => 'Get message headers',
                'params' => [],
            ],
        ]
    ];