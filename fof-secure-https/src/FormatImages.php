<?php

/*
 * This file is part of fof/secure-https.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\SecureHttps;

use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use s9e\TextFormatter\Renderer;
use s9e\TextFormatter\Utils;

class FormatImages
{
    public function __construct(protected SettingsRepositoryInterface $settings, protected UrlGenerator $url)
    {
    }

    /**
     * @param Renderer $renderer
     * @param mixed    $context
     * @param string   $xml
     *
     * @return string $xml to be unparsed
     */
    public function __invoke(Renderer $renderer, $context, string $xml)
    {
        if ((bool) $this->settings->get('fof-secure-https.proxy')) {
            $xml = Utils::replaceAttributes($xml, 'IMG', function ($attributes) {
                $attributes['src'] = $this->url->to('api')->route('fof.secure-https.imgurl').'?imgurl='.urlencode($attributes['src']);

                return $attributes;
            });
        }

        return $xml;
    }
}
