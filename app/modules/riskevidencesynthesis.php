<?php
    // =====================================================
    // RISK EVIDENCE SYNTHESIS
    // =====================================================

    return [
        'label' => 'RiskEvidenceSynthesis',
        'icon' => '⚠️',
        'description' => 'Risk evidence synthesis',
        'endpoints' => [
            [
                'key' => 'get_risksynth',
                'label' => 'Get RiskEvidenceSynthesis',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/RiskEvidenceSynthesis',
                'description' => 'Get risk evidence synthesis',
                'params' => [],
            ],
        ]
    ];