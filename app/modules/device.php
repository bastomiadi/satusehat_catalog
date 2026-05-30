<?php
    // =====================================================
    // DEVICE
    // =====================================================

    return [
        'label' => 'Device',
        'icon' => '📱',
        'description' => 'Medical devices and equipment',
        'endpoints' => [
            [
                'key' => 'get_device',
                'label' => 'Get Device',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Device',
                'description' => 'Get devices',
                'params' => [],
            ],
        ]
    ];