<?php

namespace V17Development\FlarumUserBadges\Api\Controller;

use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use V17Development\FlarumUserBadges\BadgeCategory\BadgeCategory;

class OrderBadgeCategoriesController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        RequestUtil::getActor($request)->assertAdmin();

        foreach (Arr::get($request->getParsedBody(), 'order', []) as $categoryPosition => $categoryId) {
            BadgeCategory::where('id', $categoryId)->update([
                'order' => $categoryPosition
            ]);
        }

        return new EmptyResponse();
    }
}
