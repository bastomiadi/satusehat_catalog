<?php    
    // =====================================================
    // PAYMENT NOTICE
    // =====================================================

    return [
        'label' => 'PaymentNotice',
        'icon' => '💰',
        'description' => 'Payment notices',
        'endpoints' => [
            [
                'key' => 'create_paymentnotice',
                'label' => 'Create PaymentNotice',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/PaymentNotice',
                'description' => 'Create payment notice',
                'params' => [],
                'body' => [
                    'resourceType' => 'PaymentNotice',
                    'status' => 'paid',
                    'patient' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];