<?php

if (! function_exists('generateJwtUserKey')) {
    /**
     * Generate a cache key for JWT user.
     *
     * @param  string  $token
     * @return string
     */
    function generateJwtUserKey($token)
    {
        return 'jwt-user-'.hash('sha256', $token);
    }
}
