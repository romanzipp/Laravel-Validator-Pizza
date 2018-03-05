<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database storing
    |--------------------------------------------------------------------------
    |
    | Decide wether the requested doamins & email addresses should be
    | stored to the database.
    |
    */
   
    // Database storage enabled
    'store_checks' => false,

    // Database table name
    'checks_table' => 'validator_pizza',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | It is recommended to cache requests due to API rate limitations.
    | 
    */

    // Cache enabled
    'cache_checks' => true,

    // Duration in minutes to keep the query in cache
    'cache_duration' => 30
];
