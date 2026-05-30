<?php
    // =====================================================
    // SEARCH PARAMETER
    // =====================================================

    return [
        'label' => 'SearchParameter',
        'icon' => '🔍',
        'description' => 'Search parameters',
        'endpoints' => [
            [
                'key' => 'get_searchparam',
                'label' => 'Get SearchParameter',
                'method' => 'GET',
                'path' => '/fhir-r4/v1/SearchParameter',
                'description' => 'Get search parameters',
                'params' => [],
            ],
        ]
    ];