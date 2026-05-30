<?php
    // =====================================================
    // SCHEDULE
    // =====================================================

    return [
        'label' => 'Schedule',
        'icon' => '📅',
        'description' => 'Schedules and availability',
        'endpoints' => [
            [
                'key' => 'get_schedule',
                'label' => 'Get Schedule',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Schedule',
                'description' => 'Get schedules',
                'params' => [],
            ],
        ]
    ];