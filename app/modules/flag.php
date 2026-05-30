<?php
    // =====================================================
    // FLAG
    // =====================================================

    return [
        'label' => 'Flag',
        'icon' => '🚩',
        'description' => 'Flags and alerts',
        'endpoints' => [
            [
                'key' => 'create_flag',
                'label' => 'Create Flag',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Flag',
                'description' => 'Create flag',
                'params' => [],
                'body' => [
                    'resourceType' => 'Flag',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];