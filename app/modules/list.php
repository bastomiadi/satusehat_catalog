<?php    
    // =====================================================
    // LIST
    // =====================================================

    return [
        'label' => 'List',
        'icon' => '📋',
        'description' => 'Lists and collections',
        'endpoints' => [
            [
                'key' => 'create_list',
                'label' => 'Create List',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/List',
                'description' => 'Create list',
                'params' => [],
                'body' => [
                    'resourceType' => 'List',
                    'status' => 'current',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];