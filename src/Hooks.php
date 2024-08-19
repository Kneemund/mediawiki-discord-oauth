<?php

namespace DiscordOAuth;

use DiscordOAuth\AuthenticationProvider\DiscordOAuthProvider;

class Hooks {
    public static function onWSOAuthGetAuthProviders(&$auth_providers) {
        $auth_providers['discord'] = DiscordOAuthProvider::class;
    }
}