<?php

namespace V17Development\FlarumUserBadges\Api\Resource;

use Carbon\Carbon;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use Flarum\Foundation\ValidationException;
use Flarum\Notification\NotificationSyncer;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tobyz\JsonApiServer\Context as OriginalContext;
use V17Development\FlarumUserBadges\Notification\BadgeReceivedBlueprint;
use V17Development\FlarumUserBadges\UserBadge\UserBadge;

/**
 * @extends AbstractDatabaseResource<UserBadge>
 */
class UserBadgeResource extends AbstractDatabaseResource
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected NotificationSyncer $notifications
    ) {
    }

    public function type(): string
    {
        return 'userBadges';
    }

    public function model(): string
    {
        return UserBadge::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->can('badges.canViewDetailedUsers')
                ->defaultInclude(['badge', 'user'])
                ->paginate(18),
            Endpoint\Create::make()
                ->authenticated()
                ->can('badges.giveBadge')
                ->defaultInclude(['user', 'badge.category', 'user.userBadges']),
            // Make sure the person can update a badge from another user
            Endpoint\Update::make()
                ->authenticated()
                ->can(fn (UserBadge $userBadge, Context $context) => $userBadge->user_id === $context->getActor()->id ? null : 'badges.giveBadge'),
            // Make sure the person can remove a badge from a user
            Endpoint\Delete::make()
                ->authenticated()
                ->can('badges.removeBadge'),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('description')
                ->nullable()
                ->writable(fn (UserBadge $userBadge, Context $context) => $context->getActor()->can('badges.giveBadge'))
                ->set(function (UserBadge $userBadge, ?string $description) {
                    $userBadge->description = $description !== '' ? $description : null;
                }),
            Schema\Boolean::make('isPrimary')
                ->property('is_primary')
                ->writable(fn (UserBadge $userBadge, Context $context) => $context->updating())
                ->set(function (UserBadge $userBadge, bool $isPrimary) {
                    if ($isPrimary) {
                        // Make sure there is only one primary badge
                        UserBadge::where('user_id', $userBadge->user_id)
                            ->update(['is_primary' => false]);

                        $userBadge->is_primary = true;
                    }
                }),
            Schema\DateTime::make('assignedAt')
                ->property('assigned_at'),
            Schema\Boolean::make('inUserCard')
                ->property('in_user_card'),

            Schema\Relationship\ToOne::make('badge')
                ->type('badges')
                ->includable()
                ->writableOnCreate()
                ->requiredOnCreate(),
            Schema\Relationship\ToOne::make('user')
                ->type('users')
                ->includable()
                ->writableOnCreate()
                ->requiredOnCreate(),
        ];
    }

    public function sorts(): array
    {
        return [
            SortColumn::make('assignedAt')
                ->column('assigned_at'),
        ];
    }

    public function creating(object $model, OriginalContext $context): ?object
    {
        // Make sure the user doesn't have this badge already
        if (UserBadge::where(['badge_id' => $model->badge_id, 'user_id' => $model->user_id])->exists()) {
            throw new ValidationException([
                'message' => $this->translator->trans('v17development-flarum-badges.forum.user_has_badge')
            ]);
        }

        $model->is_primary = false;
        $model->in_user_card = false;
        $model->assigned_at = Carbon::now();

        return $model;
    }

    public function created(object $model, OriginalContext $context): ?object
    {
        $this->notifications->sync(
            new BadgeReceivedBlueprint($model),
            [$model->user]
        );

        return $model;
    }
}
