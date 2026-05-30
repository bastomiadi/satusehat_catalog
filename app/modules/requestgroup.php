<?php
    // =====================================================
    // REQUEST GROUP
    // =====================================================

    return [
        'label' => 'RequestGroup',
        'icon' => '📋',
        'description' => 'Request groups',
        'endpoints' => [
            [
                'key' => 'get_requestgroup',
                'label' => 'Get RequestGroup',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/RequestGroup',
                'description' => 'Get request groups',
                'params' => [],
            ],
        ]
    ];