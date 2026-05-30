<?php
    // =====================================================
    // DEVICE METRIC
    // =====================================================

    return [
        'label' => 'DeviceMetric',
        'icon' => '📊',
        'description' => 'Device metrics and measurements',
        'endpoints' => [
            [
                'key' => 'get_devicemetric',
                'label' => 'Get DeviceMetric',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/DeviceMetric',
                'description' => 'Get device metrics',
                'params' => [],
            ],
        ]
    ];