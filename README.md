# DiscordOAuth Mediawiki Extension

An extension that adds an authentication provider for Discord to WSOAuth.

## Dependencies

### Extensions

-   [WSOAuth](https://www.mediawiki.org/wiki/Extension:WSOAuth)
-   [PluggableAuth](https://www.mediawiki.org/wiki/Extension:PluggableAuth)

### PHP (Composer)

If it doesn't already exist, create the following file in your wiki's root directory and run `composer update --no-dev` to install the required PHP dependencies.

`composer.local.json`

```json
{
    "extra": {
        "merge-plugin": {
            "include": ["extensions/*/composer.json", "skins/*/composer.json"]
        }
    }
}
```

## Minimal Setup

```php
$wgGroupPermissions['*']['autocreateaccount'] = true;

wfLoadExtension( 'PluggableAuth' );
wfLoadExtension( 'WSOAuth' );
wfLoadExtension( 'DiscordOAuth' );

$wgPluggableAuth_Config['discord'] = [
    'plugin' => 'WSOAuth',
    'data' => [
        'type' => 'discord',
        'clientId' => '<YOUR CLIENT ID>',
        'clientSecret' => '<YOUR CLIENT SECRET>'
    ],
    'buttonLabelMessage' => 'discordoauth-login-button-label'
];
```

Refer to the WSOAuth and PluggableAuth documentation for further information and configuration options.

## Configuration

| Option              | Default | Description                                                        |
| ------------------- | ------- | ------------------------------------------------------------------ |
| `$wgDiscordGuildId` | `null`  | Integer. If set, users must be in this guild to be able to log in. |
