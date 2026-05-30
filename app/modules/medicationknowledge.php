<?php    
    // =====================================================
    // MEDICATION KNOWLEDGE
    // =====================================================

    return [
        'label' => 'MedicationKnowledge',
        'icon' => '📚',
        'description' => 'Medication knowledge and information',
        'endpoints' => [
            [
                'key' => 'get_medicationknowledge',
                'label' => 'Get MedicationKnowledge by ID',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/MedicationKnowledge/{id}',
                'description' => 'Get medication knowledge by ID',
                'params' => [['name' => 'id', 'type' => 'text', 'placeholder' => 'MedicationKnowledge ID', 'default' => '']],
            ],
        ]
    ];