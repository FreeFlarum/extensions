<?php

namespace V17Development\FlarumUserBadges\BadgeCategory;

use Flarum\Database\AbstractModel;
use V17Development\FlarumUserBadges\Badge\Badge;

class BadgeCategory extends AbstractModel
{
    protected $table = 'badge_category';

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_table' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function badges()
    {
        return $this->hasMany(Badge::class);
    }
}
