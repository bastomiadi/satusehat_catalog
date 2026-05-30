<?php
    // =====================================================
    // ORGANIZATION AFFILIATION
    // =====================================================

    return [
        'label' => 'OrganizationAffiliation',
        'icon' => '🏢',
        'description' => 'Organization affiliations',
        'endpoints' => [
            [
                'key' => 'get_orgaffil',
                'label' => 'Get OrganizationAffiliation',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/OrganizationAffiliation',
                'description' => 'Get organization affiliations',
                'params' => [],
            ],
        ]
    ];