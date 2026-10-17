<?php

namespace Datlechin\OAuthSpotify\Providers;

use Flarum\Forum\Auth\Registration;
use FoF\OAuth\Provider;
use Illuminate\Support\Arr;
use League\OAuth2\Client\Provider\AbstractProvider;

class Spotify extends Provider
{
    public function name(): string
    {
        return 'spotify';
    }

    public function link(): string
    {
        return 'https://developer.spotify.com/dashboard';
    }

    public function fields(): array
    {
        return [
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
        ];
    }

    public function provider(string $redirectUri): AbstractProvider
    {
        return new SpotifyProvider([
            'clientId' => $this->getSetting('client_id'),
            'clientSecret' => $this->getSetting('client_secret'),
            'redirectUri' => $redirectUri,
        ]);
    }

    public function pkceEnabled(): bool
    {
        return false;
    }

    /**
     * Spotify does not tell whether the account email address has been verified.
     *
     * @param  SpotifyResourceOwner  $user
     */
    public function suggestions(Registration $registration, mixed $user, string $token): void
    {
        $this->verifyEmail($email = $user->getEmail());

        $registration
            ->suggestEmail($email)
            ->suggestUsername($user->getDisplayName() ?? '');

        $this->provideAvatar($registration, Arr::get($user->getImages(), '0.url'));

        $registration->setPayload($user->toArray());
    }
}
