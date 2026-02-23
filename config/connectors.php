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
    | Vega Main App  (Role Galaxy + Way AI Coach + Study Space)
    |--------------------------------------------------------------------------
    */
    'vega' => [
        'base_url' => env('VEGA_API_URL', 'https://vega.dopi.app'),
        'api_key' => env('VEGA_API_KEY'),
        'timeout' => env('VEGA_TIMEOUT', 15),
    ],

];
