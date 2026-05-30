<?php
    // =====================================================
    // OPERATION DEFINITION
    // =====================================================

    return [
        'label' => 'OperationDefinition',
        'icon' => '⚙️',
        'description' => 'Operation definitions',
        'endpoints' => [
            [
                'key' => 'get_operationdef',
                'label' => 'Get OperationDefinition',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/OperationDefinition',
                'description' => 'Get operation definitions',
                'params' => [],
            ],
        ]
    ];