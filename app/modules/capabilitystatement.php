<?php
    // =====================================================
    // CAPABILITY STATEMENT
    // =====================================================

    return [
        'label' => 'CapabilityStatement',
        'icon' => '📋',
        'description' => 'Capability statements',
        'endpoints' => [
            [
                'key' => 'get_capability',
                'label' => 'Get CapabilityStatement',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CapabilityStatement/{id}',
                'description' => 'Get capability statement',
                'params' => [['name' => 'id', 'type' => 'text', 'placeholder' => 'ID', 'default' => '']],
            ],
        ]
    ];