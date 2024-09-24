<?php

namespace DiscordOAuth\AuthenticationProvider;

use Panfu\OAuth2\Client\Provider\Discord;
use WSOAuth\AuthenticationProvider\AuthProvider;

use MediaWiki\User\UserIdentity;
use MediaWiki\MediaWikiServices;

class DiscordOAuthProvider extends AuthProvider
{
    private $provider;
    private $guildId;

    /**
     * @inheritDoc
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        ?string $authUri,
        ?string $redirectUri,
        array $extensionData = []
    ) {
        $this->provider = new Discord([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
        ]);

        $this->guildId = MediaWikiServices::getInstance()
            ->getMainConfig()
            ->get('DiscordGuildId');
    }

    /**
     * @inheritDoc
     */
    public function login(
        ?string &$key,
        ?string &$secret,
        ?string &$authUrl
    ): bool {
        $scopes = ['identify'];

        if (isset($this->guildId)) {
            $scopes[] = 'guilds.members.read';
        }

        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => $scopes,
        ]);

        $secret = $this->provider->getState();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function logout(UserIdentity &$user): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getUser(string $key, string $secret, &$errorMessage)
    {
        if (!isset($_GET['code'])) {
            $errorMessage = wfMessage('discordoauth-login-error-cancelled')->plain();
            return false;
        }

        if (empty($_GET['state']) || $_GET['state'] !== $secret) {
            $errorMessage = wfMessage('discordoauth-login-error-missing-state')->plain();
            return false;
        }

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $_GET['code'],
            ]);

            $user = $this->provider->getResourceOwner($token);

            if (isset($this->guildId)) {
                if (!$this->isUserInGuild($token->getToken())) {
                    $errorMessage = wfMessage('discordoauth-login-error-no-member')->plain();
                    return false;
                }
            }

            return [
                'name' => $user->getId(),
                'realname' => $user->getUsername(),
            ];
        } catch (\Exception $e) {
            $errorMessage = htmlspecialchars($e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function saveExtraAttributes(int $id): void
    {
    }

    private function isUserInGuild($accessToken): bool {
        $url = 'https://discord.com/api/v10/users/@me/guilds/' . $this->guildId . '/member';

        $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the body using curl_exec
        curl_setopt($ch, CURLOPT_NOBODY, true); // do not include the body in the output
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ]);

        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200) {
            return true;
        } elseif ($status === 404) {
            return false;
        } else {
            throw new Exception(wfMessage('discordoauth-login-error-discord-api')->plain());
        }
    }
}
