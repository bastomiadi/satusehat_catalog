<?php
    // =====================================================
    // DEVICE DEFINITION
    // =====================================================

    return [
        'label' => 'DeviceDefinition',
        'icon' => '📱',
        'description' => 'Device definitions and specifications',
        'endpoints' => [
            [
                'key' => 'get_devicedefinition',
                'label' => 'Get DeviceDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/DeviceDefinition',
                'description' => 'Get device definitions',
                'params' => [],
            ],
        ]
    ];