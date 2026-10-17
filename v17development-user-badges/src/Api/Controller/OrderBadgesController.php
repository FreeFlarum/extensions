<?php

namespace V17Development\FlarumUserBadges\Api\Controller;

use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use V17Development\FlarumUserBadges\Badge\Badge;

class OrderBadgesController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        RequestUtil::getActor($request)->assertAdmin();

        foreach (Arr::get($request->getParsedBody(), 'order', []) as $category) {
            foreach ($category['children'] as $badgePosition => $badgeId) {
                Badge::where('id', $badgeId)->update([
                    'order' => $badgePosition,
                    'badge_category_id' => $category['id']
                ]);
            }
        }

        return new EmptyResponse();
    }
}
