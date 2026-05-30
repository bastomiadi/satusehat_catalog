<?php    
    // =====================================================
    // EXPLANATION OF BENEFIT
    // =====================================================

    return [
        'label' => 'ExplanationOfBenefit',
        'icon' => '💰',
        'description' => 'Explanation of benefits',
        'endpoints' => [
            [
                'key' => 'get_eob',
                'label' => 'Get ExplanationOfBenefit',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ExplanationOfBenefit?patient={patient_id}',
                'description' => 'Get EOB for patient',
                'params' => [['name' => 'patient_id', 'type' => 'text', 'placeholder' => 'Patient ID', 'default' => '']],
            ],
        ]
    ];
