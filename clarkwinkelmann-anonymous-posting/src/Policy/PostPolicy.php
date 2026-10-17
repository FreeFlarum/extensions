<?php

namespace ClarkWinkelmann\AnonymousPosting\Policy;

use Carbon\Carbon;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class PostPolicy extends AbstractPolicy
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function anonymize(User $actor, Post $post)
    {
        return $post->user_id && !$post->anonymous_user_id && $actor->hasPermission('anonymous-posting.moderate');
    }

    public function deAnonymize(User $actor, Post $post)
    {
        return $post->anonymous_user_id && !$post->user_id && $actor->hasPermission('anonymous-posting.moderate');
    }

    public function edit(User $actor, Post $post)
    {
        // Same code as original but check anonymous user ID instead of regular user ID
        return $this->ownAnonymousPostAction($actor, $post, 'allow_post_editing');
    }

    public function hide(User $actor, Post $post)
    {
        // Same code as original but check anonymous user ID instead of regular user ID
        return $this->ownAnonymousPostAction($actor, $post, 'allow_hide_own_posts');
    }

    protected function ownAnonymousPostAction(User $actor, Post $post, string $settingKey)
    {
        if ($post->anonymous_user_id == $actor->id && (!$post->hidden_at || $post->hidden_user_id == $actor->id) && $actor->can('reply', $post->discussion)) {
            $allow = $this->settings->get($settingKey);

            if ($allow === '-1'
                || ($allow === 'reply' && $post->number >= $post->discussion->last_post_number)
                || (is_numeric($allow) && $post->created_at->diffInMinutes(new Carbon, true) < $allow)) {
                return $this->allow();
            }
        }

        return null;
    }
}
