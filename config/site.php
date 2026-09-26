<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Privacy policy
    |--------------------------------------------------------------------------
    |
    | The data controller named on /privacy (resources/views/privacy.blade.php).
    | The address line is left out while the address is empty.
    |
    */

    'privacy' => [
        'controller_name' => env('PRIVACY_CONTROLLER_NAME', "Snow 'n' Stuff"),
        'controller_address' => env('PRIVACY_CONTROLLER_ADDRESS'),
        'contact_email' => env('PRIVACY_CONTACT_EMAIL', 'info@1namm.com'),
    ],

];
