<?php    
    // =====================================================
    // MEDIA
    // =====================================================

    return [
        'label' => 'Media',
        'icon' => '🖼️',
        'description' => 'Media and imaging attachments',
        'endpoints' => [
            [
                'key' => 'create_media',
                'label' => 'Create Media',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Media',
                'description' => 'Create media record',
                'params' => [],
                'body' => [
                    'resourceType' => 'Media',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];