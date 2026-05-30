<?php    
    // =====================================================
    // RISK ASSESSMENT
    // =====================================================

    return [
        'label' => 'RiskAssessment',
        'icon' => '⚠️',
        'description' => 'Risk assessments and predictions',
        'endpoints' => [
            [
                'key' => 'create_riskassessment',
                'label' => 'Create RiskAssessment',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/RiskAssessment',
                'description' => 'Create risk assessment',
                'params' => [],
                'body' => [
                    'resourceType' => 'RiskAssessment',
                    'status' => 'completed',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];