<?php
    // =====================================================
    // IMMUNIZATION RECOMMENDATION
    // =====================================================

    return [
        'label' => 'ImmunizationRecommendation',
        'icon' => '💉',
        'description' => 'Immunization recommendations',
        'endpoints' => [
            [
                'key' => 'get_immunizationrec',
                'label' => 'Get ImmunizationRecommendation',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/ImmunizationRecommendation?patient={patient_id}',
                'description' => 'Get immunization recommendations',
                'params' => [['name' => 'patient_id', 'type' => 'text', 'placeholder' => 'Patient ID', 'default' => '']],
            ],
        ]
    ];