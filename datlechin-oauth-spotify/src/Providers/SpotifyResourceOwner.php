<?php

namespace Datlechin\OAuthSpotify\Providers;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class SpotifyResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    public function __construct(protected array $response)
    {
    }

    public function getId(): string
    {
        return $this->getValueByKey($this->response, 'id');
    }

    public function getDisplayName(): ?string
    {
        return $this->getValueByKey($this->response, 'display_name');
    }

    public function getEmail(): ?string
    {
        return $this->getValueByKey($this->response, 'email');
    }

    public function getExternalUrls(): array
    {
        return $this->getValueByKey($this->response, 'external_urls');
    }

    public function getHref(): string
    {
        return $this->getValueByKey($this->response, 'href');
    }

    public function getImages(): array
    {
        return $this->getValueByKey($this->response, 'images') ?? [];
    }

    public function getType(): string
    {
        return $this->getValueByKey($this->response, 'type');
    }

    public function getUri(): string
    {
        return $this->getValueByKey($this->response, 'uri');
    }

    public function getFollowers(): array
    {
        return $this->getValueByKey($this->response, 'followers');
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
