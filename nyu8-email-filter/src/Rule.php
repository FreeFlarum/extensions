<?php

namespace NYU8\FlarumEmailFilter;

use Flarum\Database\AbstractModel;

/**
 * @property int $rule_type
 * @property string $name
 * @property string $value
 * @property int $active
 */
class Rule extends AbstractModel
{
  protected $table = 'email_rules';
}
