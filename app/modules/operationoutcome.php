<?php
    // =====================================================
    // OPERATION OUTCOME
    // =====================================================

    return [
        'label' => 'OperationOutcome',
        'icon' => '❌',
        'description' => 'Operation outcomes',
        'endpoints' => [
            [
                'key' => 'get_opoutcome',
                'label' => 'Get OperationOutcome',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/OperationOutcome',
                'description' => 'Get operation outcomes',
                'params' => [],
            ],
        ]
    ];