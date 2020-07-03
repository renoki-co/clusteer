<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clusteer Endpoint
    |--------------------------------------------------------------------------
    |
    | The PHP wrapper has to connect to a specific Clusteer server, either
    | it is on the local machine or on a remote server.
    |
    | By default, it runs on localhost:8080.
    |
    */

    'endpoint' => env('CLUSTEER_ENDPOINT', 'http://localhost:8080'),

    /*
    |--------------------------------------------------------------------------
    | Clusteer Chromium Binary
    |--------------------------------------------------------------------------
    |
    | A Chromium binary is needed if the same machine runs the Node.js server.
    |
    | For Homestead environments, the default location
    | was set as /usr/bin/google-chrome-stable
    |
    */

    'chromium_path' => env('CLUSTEER_CHROMIUM_PATH', '/usr/bin/google-chrome-stable'),

];
