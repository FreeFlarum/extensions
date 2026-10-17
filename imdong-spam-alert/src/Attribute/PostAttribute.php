<?php

namespace ImDong\SpamAlert\Attribute;

use Carbon\Carbon;
use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Post\Post;
use ImDong\SpamAlert\Common\Defined;


class PostAttribute
{
    public function __invoke(): array
    {
        return [
            // 检查是否有查看的权限
            Schema\Boolean::make('canSetSpam')
                ->get(fn (Post $post, Context $context) => $context->getActor()->can(Defined::key('setSpam'), $post)),

            // 查看是否标记过 spam，写入由 SaveSpamToDatabase 处理
            Schema\Boolean::make('isSpam')
                ->get(fn (Post $post, Context $context) => $post->spams()->where('create_user_id', $context->getActor()->id)->exists())
                ->writable(fn (Post $post, Context $context) => $context->updating())
                ->set(fn () => null),

            // 是否被隐藏了
            Schema\Boolean::make('isSpamHide')
                ->visible(fn (Post $post) => (bool) $post->is_spam_hide)
                ->get(fn () => true),
        ];
    }

    /**
     * 被隐藏的 Post 在前端显示为已隐藏
     */
    public static function hiddenAt(Schema\DateTime $field): Schema\DateTime
    {
        return $field
            ->visible(fn (Post $post) => $post->hidden_at !== null || $post->is_spam_hide)
            ->get(fn (Post $post) => $post->is_spam_hide ? Carbon::createFromTimestamp(0) : $post->hidden_at);
    }
}
