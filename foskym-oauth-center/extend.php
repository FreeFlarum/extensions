<?php

/*
 * This file is part of foskym/flarum-oauth-center.
 *
 * Copyright (c) 2023 FoskyM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoskyM\OAuthCenter;

use Flarum\Extend;
use Flarum\Http\Middleware\AuthenticateWithHeader;
use Flarum\Http\Middleware\CheckCsrfToken;
use FoskyM\OAuthCenter\Api\Resource;
use FoskyM\OAuthCenter\Middlewares\ResourceScopeAuthMiddleware;
use FoskyM\OAuthCenter\Middlewares\ResourceScopeFieldsMiddleware;
use FoskyM\OAuthCenter\Middlewares\UnsetCsrfMiddleware;
use FoskyM\OAuthCenter\Middlewares\UserCredentialsMiddleware;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less')
        ->route('/oauth/authorize', 'oauth.authorize'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),
    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Routes('forum'))
        ->post('/oauth/authorize', 'oauth.authorize.post', Controllers\AuthorizeController::class)
		->post('/oauth/authorize/fetch', 'oauth.authorize.fetch', Controllers\AuthorizeFetchController::class)
        ->post('/oauth/token', 'oauth.token', Controllers\TokenController::class),

    new Extend\ApiResource(Resource\ClientResource::class),
    new Extend\ApiResource(Resource\ScopeResource::class),
    new Extend\ApiResource(Resource\AccessTokenResource::class),
    new Extend\ApiResource(Resource\RecordResource::class),

    (new Extend\Routes('api'))
        ->get('/user', 'user.show', Controllers\ApiUserController::class),

    (new Extend\Settings)
		->serializeToForum('foskym-oauth-center.display_mode', 'foskym-oauth-center.display_mode')
        ->serializeToForum('foskym-oauth-center.allow_implicit', 'foskym-oauth-center.allow_implicit', 'boolval')
        ->serializeToForum('foskym-oauth-center.enforce_state', 'foskym-oauth-center.enforce_state', 'boolval')
        ->serializeToForum('foskym-oauth-center.require_exact_redirect_uri', 'foskym-oauth-center.require_exact_redirect_uri', 'boolval')
		->serializeToForum('foskym-oauth-center.authorization_method_fetch', 'foskym-oauth-center.authorization_method_fetch', 'boolval')
        ->serializeToForum('foskym-oauth-center.allow_delete_records', 'foskym-oauth-center.allow_delete_records', 'boolval'),

    (new Extend\Middleware('api'))
        ->insertAfter(AuthenticateWithHeader::class, ResourceScopeAuthMiddleware::class)
        ->add(ResourceScopeFieldsMiddleware::class),
    (new Extend\Middleware('forum'))
        ->insertBefore(CheckCsrfToken::class, UnsetCsrfMiddleware::class)
        ->insertAfter(CheckCsrfToken::class, UserCredentialsMiddleware::class),
];
