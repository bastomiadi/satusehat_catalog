<?php
    // =====================================================
    // NUTRITION ORDER
    // =====================================================

    return [
        'label' => 'NutritionOrder',
        'icon' => '🥗',
        'description' => 'Nutrition orders and diets',
        'endpoints' => [
            [
                'key' => 'create_nutritionorder',
                'label' => 'Create NutritionOrder',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/NutritionOrder',
                'description' => 'Create nutrition order',
                'params' => [],
                'body' => [
                    'resourceType' => 'NutritionOrder',
                    'status' => 'active',
                    'subject' => ['reference' => 'Patient/{patient_id}']
                ]
            ],
        ]
    ];