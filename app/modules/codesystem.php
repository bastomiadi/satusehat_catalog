<?php
    // =====================================================
    // CODE SYSTEM
    // =====================================================

    return [
        'label' => 'CodeSystem',
        'icon' => '🔢',
        'description' => 'Code systems and terminologies',
        'endpoints' => [
            [
                'key' => 'get_codesystem',
                'label' => 'Get CodeSystem',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CodeSystem',
                'description' => 'Get code systems',
                'params' => [],
            ],
            [
                'key' => 'get_codesystem_by_id',
                'label' => 'Get CodeSystem by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CodeSystem/{id}',
                'description' => 'Get code system by ID',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'text',
                        'placeholder' => 'CodeSystem ID',
                        'default' => ''
                    ]
                ],
            ],
            [
                'key' => 'search_codesystem',
                'label' => 'Search CodeSystem',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CodeSystem?url={url}',
                'description' => 'Search code systems by URL',
                'params' => [
                    [
                        'name' => 'url',
                        'type' => 'text',
                        'placeholder' => 'URL',
                        'default' => ''
                    ]
                ],
            ],
        ]
    ];