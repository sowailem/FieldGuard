<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Policy Resolver
    |--------------------------------------------------------------------------
    |
    | If you want to use a class to resolve array-based policies
    | (like those created in the database) with custom logic, specify it here.
    | The class must implement Sowailem\FieldGuard\Contracts\PolicyResolver.
    |
    */
    'resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | You can specify a cache tag for FieldGuard rules.
    |
    */
    'cache_tag' => 'fieldguard:rules',

    /*
    |--------------------------------------------------------------------------
    | Automatic Enforcement
    |--------------------------------------------------------------------------
    |
    | When enabled, FieldGuard will automatically listen to Eloquent events
    | (retrieved, saving) to enforce field-level security without any
    | manual calls or traits.
    |
    */
    'automatic_enforcement' => false,

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the built-in RESTful API endpoints.
    |
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'field-guard',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];
