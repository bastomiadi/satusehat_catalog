<?php    
    // =====================================================
    // INVOICE
    // =====================================================

    return [
        'label' => 'Invoice',
        'icon' => '🧾',
        'description' => 'Invoices and billing',
        'endpoints' => [
            [
                'key' => 'create_invoice',
                'label' => 'Create Invoice',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Invoice',
                'description' => 'Create invoice',
                'params' => [],
                'body' => [
                    'resourceType' => 'Invoice',
                    'status' => 'balanced',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];