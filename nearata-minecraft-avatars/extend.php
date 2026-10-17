<?php

namespace Nearata\MinecraftAvatars;

use Flarum\Api\Context;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\Frontend\Document;
use Flarum\Http\RequestUtil;
use Flarum\User\Event\LoggedIn;
use Flarum\User\Event\Registered;
use Flarum\User\Event\Saving;
use Flarum\User\User;
use Nearata\MinecraftAvatars\User\Listener\LoggedInListener;
use Nearata\MinecraftAvatars\User\Listener\RegisteredListener;
use Nearata\MinecraftAvatars\User\Listener\SavingListener;
use Psr\Http\Message\ServerRequestInterface;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->route('/minecraft-avatars', 'nearata.minecraft-avatars', function (Document $document, ServerRequestInterface $request) {
            RequestUtil::getActor($request)->assertRegistered();
            $document->title = 'Minecraft Avatars';
        }),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Str::make('minotar')
                ->writable($canEdit = fn (User $user, Context $context) => $context->getActor()->id === $user->id || $context->getActor()->can('edit', $user)),
            Schema\Boolean::make('minotarEnabled')
                ->property('minotar_enabled')
                ->writable($canEdit),
        ]),

    (new Extend\Event)
        ->listen(Saving::class, SavingListener::class)
        ->listen(LoggedIn::class, LoggedInListener::class)
        ->listen(Registered::class, RegisteredListener::class),

    (new Extend\Settings)
        ->default('nearata-minecraft-avatars.retrieve_avatar_on_register', false)
];
