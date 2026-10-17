<?php

namespace ClarkWinkelmann\GroupInvitation;

use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use Illuminate\Support\Str;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends Resource\AbstractDatabaseResource<Invitation>
 */
class InvitationResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'group-invitations';
    }

    public function model(): string
    {
        return Invitation::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->admin()
                ->defaultInclude(['group'])
                ->defaultSort('createdAt'),
            // Intentionally no authorization, anyone can check an invitation
            Endpoint\Show::make()
                ->defaultInclude(['group']),
            Endpoint\Create::make()
                ->admin()
                ->defaultInclude(['group']),
            Endpoint\Delete::make()
                ->admin(),
        ];
    }

    /**
     * The show endpoint receives the invitation code instead of the ID.
     */
    public function find(string $id, OriginalContext $context): ?object
    {
        if ($context->endpoint instanceof Endpoint\Show) {
            return $this->query($context)->where('code', $id)->first();
        }

        return parent::find($id, $context);
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('code'),
            Schema\Boolean::make('hasUsagesLeft')
                ->get(fn (Invitation $invitation) => $invitation->hasUsagesLeft()),
            Schema\Boolean::make('canUse')
                ->get(fn (Invitation $invitation, Context $context) => $context->getActor()->can('use', $invitation)),
            Schema\Integer::make('usageCount')
                ->visible(fn (Invitation $invitation, Context $context) => $context->getActor()->isAdmin()),
            Schema\Integer::make('maxUsage')
                ->visible(fn (Invitation $invitation, Context $context) => $context->getActor()->isAdmin())
                ->writableOnCreate()
                ->nullable()
                ->rule('min:1'),
            Schema\DateTime::make('createdAt')
                ->visible(fn (Invitation $invitation, Context $context) => $context->getActor()->isAdmin()),

            Schema\Relationship\ToOne::make('group')
                ->includable()
                ->writableOnCreate()
                ->requiredOnCreate()
                ->type('groups')
                // Like in v1, the invited group is shown even if it's hidden from the actor
                ->get(fn (Invitation $invitation) => $invitation->group()->first()),
        ];
    }

    public function sorts(): array
    {
        return [
            SortColumn::make('createdAt'),
        ];
    }

    public function creating(object $model, OriginalContext $context): ?object
    {
        $model->code = Str::random();
        $model->usage_count = 0;

        return $model;
    }
}
