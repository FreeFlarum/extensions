<?php

namespace Nearata\MinecraftAvatars;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class Helpers
{
    public static $usernames = ['MHF_Steve', 'MHF_Alex'];

    public static function isUsername(string $minotar): bool
    {
        $validUsernameRegex = '/^[a-zA-Z0-9_]{1,16}$/';

        return preg_match($validUsernameRegex, $minotar);
    }

    public static function getUUID(string $username): string
    {
        $response = (new Client(['timeout' => 30, 'http_errors' => false]))->get('https://api.mojang.com/users/profiles/minecraft/'.$username);

        if ($response->getStatusCode() !== 200) {
            return '';
        }

        return json_decode((string) $response->getBody(), true)['id'] ?? '';
    }

    public static function getRandomUsername(): string
    {
        return Arr::random(Helpers::$usernames);
    }

    public static function isUUID(string $minotar): bool
    {
        $validUUIDPlainRegex = '[0-9a-f]{32}';
        $validUUIDDashRegex = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';
        $validUUIDRegex = '/^('.$validUUIDPlainRegex.'|'.$validUUIDDashRegex.')$/';

        return preg_match($validUUIDRegex, $minotar);
    }
}
