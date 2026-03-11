<?php

use App\Libraries\Authz;

if (! function_exists('authz')) {
    function authz(): Authz
    {
        return new Authz(db_connect(), service('session'));
    }
}

if (! function_exists('can')) {
    function can(string $permissionKey): bool
    {
        return authz()->can($permissionKey);
    }
}

