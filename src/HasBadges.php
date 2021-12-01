<?php

namespace Jawabapp\Gamify;

use Jawabapp\Gamify\Events\BadgeEarned;
use Illuminate\Support\Facades\Log;

trait HasBadges
{
    /**
     * Badges user relation
     *
     * @return mixed
     */
    public function badges()
    {
        $table_name = app(config('gamify.payee_model'))->getTable() ?? 'users';
        $linkable_column_name = snake_case(str_singular($table_name));

        return $this->belongsToMany(
            Badge::class,
            $linkable_column_name . '_badges',
            $linkable_column_name . '_id',
            'badge_id'
        )
            ->withTimestamps();
    }

    /**
     * Sync badges for qiven user
     *
     * @param $user
     */
    public function syncBadges($user = null)
    {
        $user = is_null($user) ? $this : $user;

        $badgeIds = app('badges')->filter
            ->qualifier($user)
            ->map
            ->getBadgeId()
            ->toArray();

        $data = $user->badges()->sync($badgeIds);

        $new_badges = Badge::whereIn('id', $data['attached'] ?? [])->get();

        foreach ($new_badges as $new_badge) {
            event(new BadgeEarned($user, $new_badge));
        }
    }
}
