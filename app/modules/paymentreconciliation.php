<?php    
    // =====================================================
    // PAYMENT RECONCILIATION
    // =====================================================

    return [
        'label' => 'PaymentReconciliation',
        'icon' => '💰',
        'description' => 'Payment reconciliation',
        'endpoints' => [
            [
                'key' => 'create_paymentrecon',
                'label' => 'Create PaymentReconciliation',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/PaymentReconciliation',
                'description' => 'Create payment reconciliation',
                'params' => [],
                'body' => [
                    'resourceType' => 'PaymentReconciliation',
                    'status' => 'completed'
                ]
            ],
        ]
    ];