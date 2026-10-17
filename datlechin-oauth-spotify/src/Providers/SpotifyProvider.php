<?php

namespace Datlechin\OAuthSpotify\Providers;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class SpotifyProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected string $spotifyUrl = 'https://accounts.spotify.com';

    public function getBaseAuthorizationUrl(): string
    {
        return $this->spotifyUrl . '/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->spotifyUrl . '/api/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.spotify.com/v1/me';
    }

    protected function getDefaultScopes(): array
    {
        return [
            SpotifyScope::USER_READ_PRIVATE,
            SpotifyScope::USER_READ_EMAIL,
        ];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['error_description'] ?? $data['error'] ?? $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new SpotifyResourceOwner($response);
    }
}
