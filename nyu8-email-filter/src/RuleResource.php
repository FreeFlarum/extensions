<?php

namespace NYU8\FlarumEmailFilter;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;

/**
 * @extends Resource\AbstractDatabaseResource<Rule>
 */
class RuleResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'email_rules';
    }

    public function model(): string
    {
        return Rule::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->admin(),
            Endpoint\Create::make()
                ->admin(),
            Endpoint\Update::make()
                ->admin(),
            Endpoint\Delete::make()
                ->admin(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Integer::make('ruleType')
                ->property('rule_type')
                ->requiredOnCreate()
                ->writableOnCreate(),
            Schema\Str::make('name')
                ->requiredOnCreate()
                ->writable()
                ->set(function (Rule $rule, ?string $value) {
                    if ($value) {
                        $rule->name = $value;
                    }
                }),
            Schema\Str::make('value')
                ->requiredOnCreate()
                ->writable()
                ->set(function (Rule $rule, ?string $value) {
                    if ($value) {
                        $rule->value = $value;
                    }
                }),
            Schema\Integer::make('active')
                ->requiredOnCreate()
                ->writable()
                ->set(function (Rule $rule, int $value) {
                    $rule->active = $value > 0 ? 1 : 0;
                }),
        ];
    }
}
