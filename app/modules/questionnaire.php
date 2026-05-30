<?php    
    // =====================================================
    // QUESTIONNAIRE
    // =====================================================

    return [
        'label' => 'Questionnaire',
        'icon' => '❓',
        'description' => 'Questionnaires and surveys',
        'endpoints' => [
            [
                'key' => 'create_questionnaire',
                'label' => 'Create Questionnaire',
                'method' => 'POST',
                'path' => '/fhir-r4/v1/Questionnaire',
                'description' => 'Create questionnaire',
                'params' => [],
                'body' => [
                    'resourceType' => 'Questionnaire',
                    'status' => 'active',
                    'title' => '',
                    'subjectType' => ['Patient']
                ]
            ],
        ]
    ];