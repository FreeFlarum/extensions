<?php

namespace ClarkWinkelmann\GroupList;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use Flarum\Formatter\Formatter;

/**
 * @extends Resource\AbstractDatabaseResource<GroupListItem>
 */
class GroupListItemResource extends Resource\AbstractDatabaseResource
{
    public function __construct(
        protected Formatter $formatter
    ) {
    }

    public function type(): string
    {
        return 'clarkwinkelmann-group-list-items';
    }

    public function model(): string
    {
        return GroupListItem::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->can('clarkwinkelmann-group-list.see')
                ->defaultInclude(['group', 'members.groups'])
                ->defaultSort('order'),
            Endpoint\Create::make()
                ->admin()
                ->defaultInclude(['group']),
            Endpoint\Update::make()
                ->admin()
                ->defaultInclude(['group']),
            Endpoint\Delete::make()
                ->admin(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('content')
                ->nullable()
                ->writableOnUpdate()
                ->get(fn (GroupListItem $item) => $item->content ? $this->formatter->unparse($item->content) : null)
                ->set(function (GroupListItem $item, ?string $value) {
                    $item->content = $this->formatter->parse((string) $value);
                }),
            Schema\Str::make('contentHtml')
                ->get(fn (GroupListItem $item) => $item->content ? $this->formatter->render($item->content) : null),
            Schema\Integer::make('order')
                ->nullable()
                ->writable(),

            Schema\Relationship\ToOne::make('group')
                ->includable()
                ->writableOnCreate()
                ->requiredOnCreate()
                ->type('groups')
                // Like in v1, listed groups are shown even if they are hidden from the actor
                ->get(fn (GroupListItem $item) => $item->group()->first()),
            Schema\Relationship\ToMany::make('members')
                ->includable()
                ->type('users'),
        ];
    }

    public function sorts(): array
    {
        return [
            SortColumn::make('order'),
        ];
    }
}
