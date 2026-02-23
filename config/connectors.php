<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mission Way
    |--------------------------------------------------------------------------
    */
    'mission_way' => [
        'base_url' => env('MISSION_WAY_URL', 'https://way-backend.dopingtech.net'),
        'api_key' => env('MISSION_WAY_API_KEY'),
        'timeout' => env('MISSION_WAY_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Way Startup
    |--------------------------------------------------------------------------
    */
    'way_startup' => [
        'base_url' => env('WAY_STARTUP_URL', 'https://way-backend.dopingtech.net'),
        'api_key' => env('WAY_STARTUP_API_KEY'),
        'timeout' => env('WAY_STARTUP_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Galaxy
    |--------------------------------------------------------------------------
    */
    'role_galaxy' => [
        'base_url' => env('ROLE_GALAXY_URL'),
        'api_key' => env('ROLE_GALAXY_API_KEY'),
        'timeout' => env('ROLE_GALAXY_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Way AI Coach
    |--------------------------------------------------------------------------
    */
    'way_ai_coach' => [
        'base_url' => env('WAY_AI_COACH_URL'),
        'api_key' => env('WAY_AI_COACH_API_KEY'),
        'timeout' => env('WAY_AI_COACH_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Study Space
    |--------------------------------------------------------------------------
    */
    'study_space' => [
        'base_url' => env('STUDY_SPACE_URL'),
        'api_key' => env('STUDY_SPACE_API_KEY'),
        'timeout' => env('STUDY_SPACE_TIMEOUT', 10),
    ],

];
