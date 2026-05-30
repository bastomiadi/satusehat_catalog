<?php
    // =====================================================
    // PERSON
    // =====================================================

    return [
        'label' => 'Person',
        'icon' => '👤',
        'description' => 'Persons and individuals',
        'endpoints' => [
            [
                'key' => 'get_person',
                'label' => 'Get Person',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/Person',
                'description' => 'Get persons',
                'params' => [],
            ],
        ]
    ];