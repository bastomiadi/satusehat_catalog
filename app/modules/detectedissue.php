<?php
    // =====================================================
    // DETECTED ISSUE
    // =====================================================

    return [
        'label' => 'DetectedIssue',
        'icon' => '⚠️',
        'description' => 'Detected issues and clinical warnings',
        'endpoints' => [
            [
                'key' => 'create_detectedissue',
                'label' => 'Create DetectedIssue',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/DetectedIssue',
                'description' => 'Create detected issue',
                'params' => [],
                'body' => [
                    'resourceType' => 'DetectedIssue',
                    'status' => 'final',
                    'code' => [
                        'coding' => [
                            ['system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode', 'code' => '', 'display' => '']
                        ]
                    ],
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];