<?php

/*
 * This file is part of foskym/flarum-custom-levels.
 *
 * Copyright (c) 2024 FoskyM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
namespace FoskyM\CustomLevels\Command;

use Flarum\User\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Post\Event\Posted;
use Flarum\Post\Event\Restored as PostRestored;
use Flarum\Post\Event\Hidden as PostHidden;
use Flarum\Post\Event\Deleted as PostDeleted;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Started;
use Flarum\Discussion\Event\Restored as DiscussionRestored;
use Flarum\Discussion\Event\Hidden as DiscussionHidden;
use Flarum\Discussion\Event\Deleted as DiscussionDeleted;
use Flarum\User\Event\Saving;
use Flarum\Likes\Event\PostWasLiked;
use Flarum\Likes\Event\PostWasUnliked;
use FoskyM\CustomLevels\Event\LevelUpdated;
use FoskyM\CustomLevels\Event\ExpUpdated;
use FoskyM\CustomLevels\AutoRemoveEnum;
use FoskyM\CustomLevels\ExpValue;
use FoskyM\CustomLevels\Model\Level;
use FoskyM\CustomLevels\Model\ExpLog;
use Carbon\Carbon;

class RefreshExpCommand extends Command
{
    protected $signature = 'foskym:custom-levels:refresh';
    protected $description = 'Re-computes the exp for each user';
    protected $exp_for_post;
    protected $post_min_length;
    protected $exp_for_discussion;
    protected $exp_for_like;

    public function __construct(protected SettingsRepositoryInterface $settings, protected Dispatcher $events)
    {
        $this->exp_for_post = $this->settings->get('foskym-custom-levels.expForPost');
        $this->post_min_length = (int) $this->settings->get('foskym-custom-levels.postMinLength', 0);
        $this->exp_for_discussion = $this->settings->get('foskym-custom-levels.expForDiscussion');
        $this->exp_for_like = $this->settings->get('foskym-custom-levels.expForLike');

        parent::__construct();
    }

    private function expLog($user, $exp, $recent_exp, $current_exp, $type)
    {
        $exp_log = new ExpLog();
        $exp_log->user_id = $user->id;
        $exp_log->exp = $exp;
        $exp_log->old_exp = $recent_exp;
        $exp_log->new_exp = $current_exp;
        $exp_log->type = $type;
        $exp_log->relationship = [];
        $exp_log->created_at = Carbon::now();
        $exp_log->save();
    }
    public function handle()
    {
        $progress = $this->output->createProgressBar(User::query()->count());

        User::query()->chunk(100, function (Collection $users) use ($progress) {
            $users->load(['posts' => function (HasMany $relationship) {
                $relationship->where('type', 'comment')
                    ->where('is_private', false);
            }]);

            foreach ($users as $user) {
                $this->expLog($user, - $user->exp, $user->exp, 0, 'system_clear');
                $user->exp = 0;
                foreach ($user->posts as $post) {
                    if ($post->number == 1) {
                        $user->exp += ExpValue::roll($this->exp_for_discussion);
                    }
                    if (strlen($post->content) >= $this->post_min_length) {
                        $user->exp += ExpValue::roll($this->exp_for_post);
                    }
                }

                $user->posts->loadCount('likes');

                $user->exp += $user->posts->sum('likes_count') * ExpValue::roll($this->exp_for_like);
                $user->save();

                $this->expLog($user, $user->exp, 0, $user->exp, 'system_refresh');

                $progress->advance();
            }
        });

        $this->line('Done.');
    }
}