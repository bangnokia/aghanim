<?php

return [
    'action_namespace' => 'App\\Actions',
    'action_path' => app_path('Actions'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to the Aghanim routes.
    | By default, we use the web middleware group and auth middleware.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Authorized Actions
    |--------------------------------------------------------------------------
    |
    | This option controls which actions can be called from the frontend.
    | Set to 'all' to allow all actions, or provide an array of action class names
    | to restrict to specific actions.
    |
    | Example: ['App\\Actions\\GetUsers', 'App\\Actions\\CreatePost']
    |
    */
    'authorized_actions' => 'all',

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    |
    | Determines whether CSRF protection is enabled for Aghanim requests.
    | It's recommended to keep this enabled for security.
    |
    */
    'csrf_protection' => true,
];
