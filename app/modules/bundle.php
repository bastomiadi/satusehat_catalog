<?php
    // =====================================================
    // BUNDLE
    // =====================================================

    return [
        'label' => 'Bundle',
        'icon' => '📦',
        'description' => 'FHIR transaction bundle',
        'endpoints' => [

            [
                'key' => 'create_bundle',
                'label' => 'Create Transaction Bundle',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Bundle',
                'description' => 'Create FHIR bundle',
                'params' => [],
                'body' => [
                    'resourceType' => 'Bundle',
                    'type' => 'transaction',
                    'entry' => []
                ]
            ],

        ]
    ];