<?php
// =====================================================
    // ADVERSE EVENT
    // =====================================================

    return [
        'label' => 'AdverseEvent',
        'icon' => '⚠️',
        'description' => 'Adverse events and safety reports',
        'endpoints' => [
            [
                'key' => 'create_adverseevent',
                'label' => 'Create AdverseEvent',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/AdverseEvent',
                'description' => 'Create adverse event',
                'params' => [],
                'body' => [
                    'resourceType' => 'AdverseEvent',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];