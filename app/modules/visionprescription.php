<?php    
    // =====================================================
    // VISION PRESCRIPTION
    // =====================================================

    return [
        'label' => 'VisionPrescription',
        'icon' => '👓',
        'description' => 'Vision prescriptions',
        'endpoints' => [
            [
                'key' => 'create_visionprescription',
                'label' => 'Create VisionPrescription',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/VisionPrescription',
                'description' => 'Create vision prescription',
                'params' => [],
                'body' => [
                    'resourceType' => 'VisionPrescription',
                    'status' => 'active',
                    'patient' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];