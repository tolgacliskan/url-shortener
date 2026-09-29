<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Social Authentication
    |--------------------------------------------------------------------------
    |
    | Whether "Continue with Google" and "Continue with GitHub" are offered on
    | the login and register pages.
    |
    | A provider needs both: the switch here, and credentials in services.php.
    | The credential check is what keeps a self-hosted install from rendering a
    | button that leads straight to an OAuth error, and the switch is what lets
    | you turn a provider off without deleting the credentials to do it.
    |
    | Default true, so configuring credentials is enough to get the button.
    |
    */

    'auth' => [
        'google' => (bool) env('GOOGLE_AUTH_ENABLED', false),
        'github' => (bool) env('GITHUB_AUTH_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    |
    | Whether anyone can open an account: the register page, and a first
    | sign-in through Google or GitHub, which creates one too.
    |
    | Off, /register sends visitors to the login page and new users only come
    | in through an invite or `php artisan lua:create-user`.
    |
    */

    'registration' => (bool) env('REGISTRATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Domain Announcements
    |--------------------------------------------------------------------------
    |
    | Whether each custom domain is written to Redis as it is added, renamed
    | or removed, for a proxy in front of the app to read. Nothing in the app
    | reads these keys; an install whose web server already routes its own
    | domains has no use for them, and no Redis to write them to.
    |
    */

    'announce_domains' => false,

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | How many rows a list asks for. The page size never comes from the
    | request: a caller cannot widen it, and every list action reads this.
    |
    | The public REST API is the exception — it has its own fixed, documented
    | page size, because changing this must not change an API contract.
    |
    */

    'pagination' => [
        'default' => (int) env('LUA_PAGINATION_DEFAULT', 20),
    ],

];
