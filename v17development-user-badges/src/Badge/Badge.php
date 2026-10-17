<?php

namespace V17Development\FlarumUserBadges\Badge;

use Flarum\Database\AbstractModel;
use V17Development\FlarumUserBadges\BadgeCategory\BadgeCategory;
use V17Development\FlarumUserBadges\UserBadge\UserBadge;

class Badge extends AbstractModel
{
    protected $table = 'badges';

    protected $casts = [
        'is_visible' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(BadgeCategory::class, 'badge_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(UserBadge::class, 'badge_id');
    }

    /**
     * User count
     */
    public function earnedCount()
    {
        return $this->hasMany(UserBadge::class, 'badge_id')->count();
    }
}
