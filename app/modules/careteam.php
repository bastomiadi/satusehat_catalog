<?php
    // =====================================================
    // CARE TEAM
    // =====================================================

    return [
        'label' => 'CareTeam',
        'icon' => '👥',
        'description' => 'Care team members and roles',
        'endpoints' => [
            [
                'key' => 'create_careteam',
                'label' => 'Create CareTeam',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/CareTeam',
                'description' => 'Create care team',
                'params' => [],
                'body' => [
                    'resourceType' => 'CareTeam',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
            [
                'key' => 'get_careteam',
                'label' => 'Get CareTeam by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/CareTeam?subject={patient_id}',
                'description' => 'Get care teams for patient',
                'params' => [['name' => 'patient_id', 'type' => 'text', 'placeholder' => 'Patient ID', 'default' => '']],
            ],
        ]
    ];