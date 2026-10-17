<?php

namespace ClarkWinkelmann\CatchTheFish\Api\Resource;

use ClarkWinkelmann\CatchTheFish\Fish;
use ClarkWinkelmann\CatchTheFish\Repositories\FishRepository;
use ClarkWinkelmann\CatchTheFish\Repositories\PlacementRepository;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Flarum\Foundation\ValidationException;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends AbstractDatabaseResource<Fish>
 */
class FishResource extends AbstractDatabaseResource
{
    public function __construct(
        protected FishRepository $fishes,
        protected PlacementRepository $placement
    )
    {
    }

    public function type(): string
    {
        return 'catchthefish-fishes';
    }

    public function model(): string
    {
        return Fish::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Endpoint::make('update')
                ->route('PATCH', '/{id}')
                ->can('update')
                ->action(function (Context $context) {
                    return $this->fishes->update($context->model, Arr::get($context->body(), 'data.attributes', []));
                }),
            Endpoint\Delete::make()
                ->can('delete'),
            Endpoint\Endpoint::make('catch')
                ->route('POST', '/{id}/catch')
                ->defaultInclude(['round.myRanking'])
                ->action(function (Context $context) {
                    return $this->placement->catch($context->getActor(), $context->model, (array)$context->body());
                }),
            Endpoint\Endpoint::make('place')
                ->route('POST', '/{id}/place')
                ->action(function (Context $context) {
                    return $this->placement->place($context->getActor(), $context->model, (array)$context->body());
                }),
            Endpoint\Endpoint::make('image')
                ->route('POST', '/{id}/image')
                ->can('update')
                ->action(function (Context $context) {
                    return $this->fishes->updateImage($context->model, Arr::get($context->request->getUploadedFiles(), 'image'));
                }),
        ];
    }

    /**
     * Handle ValidationExceptions in policy as a denied policy
     */
    protected function actorCan(User $actor, string $ability, ?Fish $fish = null): bool
    {
        try {
            return $actor->can($ability, $fish);
        } catch (ValidationException $exception) {
            return false;
        }
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('name'),
            Schema\Str::make('image_url')
                ->nullable()
                ->get(fn(Fish $fish) => $fish->image_url),
            Schema\Arr::make('placement')
                ->nullable()
                ->get(function (Fish $fish, Context $context) {
                    $actor = $context->getActor();

                    return $this->actorCan($actor, 'catch', $fish) || $this->actorCan($actor, 'catchthefish.moderate') ? [
                        'discussion_id' => $fish->discussion_id_placement,
                        'post_id' => $fish->post_id_placement,
                        'user_id' => $fish->user_id_placement,
                    ] : null;
                }),
            Schema\Boolean::make('canSee')
                ->get(fn(Fish $fish, Context $context) => $this->actorCan($context->getActor(), 'see', $fish)),
            Schema\Boolean::make('canCatch')
                ->get(fn(Fish $fish, Context $context) => $this->actorCan($context->getActor(), 'catch', $fish)),
            Schema\Boolean::make('canName')
                ->get(fn(Fish $fish, Context $context) => $this->actorCan($context->getActor(), 'name', $fish)),
            Schema\Boolean::make('canPlace')
                ->get(fn(Fish $fish, Context $context) => $this->actorCan($context->getActor(), 'place', $fish)),
            Schema\DateTime::make('placeUntil')
                ->nullable()
                ->get(fn(Fish $fish, Context $context) => $this->actorCan($context->getActor(), 'place', $fish) ? $fish->placement_valid_since : null),

            Schema\Relationship\ToOne::make('round')
                ->type('catchthefish-rounds')
                ->includable()
                ->withoutLinkage(),
            Schema\Relationship\ToOne::make('lastUserPlacement')
                ->type('users')
                ->includable()
                ->withoutLinkage(),
            Schema\Relationship\ToOne::make('lastUserNaming')
                ->type('users')
                ->includable()
                ->withoutLinkage(),
            Schema\Relationship\ToOne::make('placementModel')
                ->collection(['discussions', 'posts', 'users'])
                ->includable()
                ->withoutLinkage()
                ->visible(fn(Fish $fish, Context $context) => $context->getActor()->can('catchthefish.moderate'))
                ->get(function (Fish $fish, Context $context) {
                    $actor = $context->getActor();

                    if ($fish->discussion_id_placement) {
                        return $fish->placementDiscussion()->whereVisibleTo($actor)->first();
                    }

                    if ($fish->post_id_placement) {
                        return $fish->placementPost()->whereVisibleTo($actor)->first();
                    }

                    if ($fish->user_id_placement) {
                        return $fish->placementUser()->whereVisibleTo($actor)->first();
                    }

                    return null;
                }),
        ];
    }

    /**
     * @param Fish $model
     */
    public function delete(object $model, OriginalContext $context): void
    {
        $this->fishes->delete($model);
    }
}
