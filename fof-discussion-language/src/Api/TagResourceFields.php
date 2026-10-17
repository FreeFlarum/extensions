<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api;

use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use Flarum\Tags\Tag;

class TagResourceFields
{
    public function __invoke(): array
    {
        return [
            Schema\Arr::make('localisedLastDiscussion')
                ->nullable()
                ->get(function (Tag $tag) {
                    $json = json_decode($tag->localised_last_discussion ?? '', true);

                    // Attach discussion title as this is needed for the tags page
                    if ($json) {
                        $ids = collect($json)->pluck('id')->filter()->unique()->values()->all();
                        $discussions = Discussion::whereIn('id', $ids)->get()->keyBy('id');

                        foreach ($json as $languageId => $data) {
                            $data['title'] = optional($discussions->get($data['id']))->title ?: '';
                            $json[$languageId] = $data;
                        }
                    }

                    return $json;
                }),
        ];
    }
}
