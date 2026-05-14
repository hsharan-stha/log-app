<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default login password for new staff
    |--------------------------------------------------------------------------
    |
    | Applied when creating staff (single or bulk). Staff can sign in at /login
    | to register their face at /my-face, then change password via profile if needed.
    |
    */
    'default_password' => env('STAFF_DEFAULT_PASSWORD', 'password'),

];
