<?php

namespace FoskyM\CustomLevels\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\User\User;
use FoskyM\CustomLevels\Model\Level;
use Illuminate\Database\Eloquent\Collection;

class UserResourceFields
{
    protected ?Collection $levels = null;

    public function __invoke(): array
    {
        return [
            Schema\Integer::make('exp')
                ->get(fn (User $user) => (int) $user->exp)
                ->writable(fn (User $user, Context $context) => $context->getActor()->can('edit_exp', $user)),
            Schema\Boolean::make('canEditExp')
                ->get(fn (User $user, Context $context) => $context->getActor()->can('edit_exp', $user)),
            Schema\Attribute::make('expLevel')
                ->get(fn (User $user) => $this->compute($user)['expLevel']),
            Schema\Integer::make('expTotal')
                ->get(fn (User $user) => $this->compute($user)['expTotal']),
            Schema\Number::make('expPercent')
                ->get(fn (User $user) => $this->compute($user)['expPercent']),
            Schema\Str::make('expNext')
                ->get(fn (User $user) => $this->compute($user)['expNext']),
            Schema\Integer::make('expNextNeed')
                ->get(fn (User $user) => $this->compute($user)['expNextNeed']),
        ];
    }

    protected function compute(User $user): array
    {
        $expLevel = 0;
        $expTotal = 0;
        $expPercent = 0;
        $expNext = '';
        $expNextNeed = 0;

        try {
            $expTotal = $user->exp;
            $levels = $this->levels ??= Level::orderBy('min_exp_required', 'desc')->get();
            $level = $levels->where('min_exp_required', '<=', $expTotal)->sortByDesc('min_exp_required')->first();

            if ($level) {
                $expLevel = $level->name;
            }

            $levelNext = $levels->where('min_exp_required', '>', $expTotal)->sortBy('min_exp_required')->first();

            if ($levelNext) {
                $expPercent = $level?->min_exp_required ? round(($expTotal - $level->min_exp_required) / ($levelNext->min_exp_required - $level->min_exp_required) * 100) : 0;
                $expNext = $levelNext->name;
                $expNextNeed = $levelNext->min_exp_required - $expTotal;
            } else {
                $expNext = $levels->first()?->name;
                $expNextNeed = ($levels->first()?->min_exp_required ?? 0) - $expTotal;
                if ($expNextNeed < 0) {
                    $expNextNeed = 0;
                    $expNext = '-';
                    $expPercent = 100;
                }
            }

        } catch (\Throwable $e) {
            $expLevel = -1;
            $expTotal = -1;
            $expPercent = -1;
            $expNext = '';
            $expNextNeed = -1;
        }

        return [
            'expLevel' => $expLevel,
            'expTotal' => $expTotal,
            'expPercent' => $expPercent,
            'expNext' => $expNext,
            'expNextNeed' => $expNextNeed
        ];
    }
}
