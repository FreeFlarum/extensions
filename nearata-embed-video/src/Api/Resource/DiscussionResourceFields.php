<?php

namespace Nearata\EmbedVideo\Api\Resource;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;

class DiscussionResourceFields
{
    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('canNearataEmbedVideoView')
                ->get(fn (Discussion $discussion, Context $context) => $context->getActor()->can('nearata-embed-video.view', $discussion)),
        ];
    }
}
