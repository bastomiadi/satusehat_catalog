<?php
    // =====================================================    
    // DOCUMENT REFERENCE
    // =====================================================

    return [
        'label' => 'DocumentReference',
        'icon' => '📄',
        'description' => 'Document references',
        'endpoints' => [

            [
                'key' => 'create_document_reference',
                'label' => 'Create DocumentReference',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/DocumentReference',
                'description' => 'Create document reference',
                'params' => [],
                'body' => [
                    'resourceType' => 'DocumentReference',
                    'status' => 'current',

                    'subject' => [
                        'reference' => 'Patient/{patient_id}'
                    ],

                    'content' => [
                        [
                            'attachment' => [
                                'contentType' => 'application/pdf',
                                'url' => ''
                            ]
                        ]
                    ]
                ]
            ],

        ]
    ];