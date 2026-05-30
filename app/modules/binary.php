<?php
    // =====================================================
    // BINARY
    // =====================================================

    return [
        'label' => 'Binary',
        'icon' => '📁',
        'description' => 'Binary data and attachments',
        'endpoints' => [
            [
                'key' => 'get_binary',
                'label' => 'Get Binary',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Binary/{id}',
                'description' => 'Get binary data',
                'params' => [['name' => 'id', 'type' => 'text', 'placeholder' => 'Binary ID', 'default' => '']],
            ],
        ]
    ];