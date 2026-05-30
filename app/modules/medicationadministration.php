<?php    
    // =====================================================
    // MEDICATION ADMINISTRATION
    // =====================================================

    return [
        'label' => 'MedicationAdministration',
        'icon' => '💉',
        'description' => 'Medication administration records',
        'endpoints' => [
            [
                'key' => 'get_medicationadministration',
                'label' => 'Get MedicationAdministration by Patient',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationAdministration?subject={patient_id}',
                'description' => 'Get medication administrations for patient',
                'params' => [['name' => 'patient_id', 'type' => 'text', 'placeholder' => 'Patient ID', 'default' => '']],
            ],
        ]
    ];