<?php

namespace ClarkWinkelmann\AnonymousPosting\FakeUserRelation;

use ClarkWinkelmann\AnonymousPosting\AnonymousUser;
use Closure;
use Flarum\Discussion\Discussion;
use Flarum\Mentions\Notification\PostMentionedBlueprint;
use Flarum\Notification\Job\SendEmailNotificationJob;
use Flarum\Post\CommentPost;
use ReflectionProperty;

class FakeUserHydratorPipe
{
    public function handle($command, Closure $next)
    {
        if (!($command instanceof SendEmailNotificationJob)) {
            return $next($command);
        }

        // The blueprint is a private property of the job without any getter
        $blueprint = (new ReflectionProperty(SendEmailNotificationJob::class, 'blueprint'))->getValue($command);

        // Post mentions have a special "reply" attribute that isn't exposed through the regular interface
        // But its ->user will be accessed in ->getFromUser(), so we need to also update it
        if ($blueprint instanceof PostMentionedBlueprint && $blueprint->reply->anonymous_user_id) {
            $blueprint->reply->setRelation('user', new AnonymousUser());
        }

        $subject = $blueprint->getSubject();

        if (($subject instanceof CommentPost || $subject instanceof Discussion) && $subject->anonymous_user_id) {
            $subject->setRelation('user', new AnonymousUser());
        }

        return $next($command);
    }
}
