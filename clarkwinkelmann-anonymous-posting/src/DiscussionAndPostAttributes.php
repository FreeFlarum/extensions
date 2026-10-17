<?php

namespace ClarkWinkelmann\AnonymousPosting;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Kilowhat\Formulaire\Submission;

class DiscussionAndPostAttributes
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('isAnonymous')
                ->get(fn (AbstractModel $model) => !!$model->anonymous_user_id)
                ->writable()
                // The value is handled by the Saving event listeners
                ->set(fn () => null),
            // Both permission keys need to be included all the time for the frontend to correctly update state after switching
            Schema\Boolean::make('canDeAnonymize')
                ->get(fn (AbstractModel $model, Context $context) => $context->getActor()->can('deAnonymize', $model)),
            Schema\Boolean::make('canAnonymize')
                ->get(fn (AbstractModel $model, Context $context) => $context->getActor()->can('anonymize', $model)),
            Schema\Str::make('anonymousAvatarUrl')
                ->nullable()
                ->visible(fn (AbstractModel $model) => !!$model->anonymous_user_id)
                ->get(fn (AbstractModel $model) => $this->avatarUrl($model)),
            Schema\Boolean::make('isAnonymousMe')
                ->visible(fn (AbstractModel $model) => !!$model->anonymous_user_id)
                ->get(fn (AbstractModel $model, Context $context) => $model->anonymous_user_id === $context->getActor()->id),
            Schema\Relationship\ToOne::make('anonymousUser')
                ->type('users')
                ->includable()
                ->visible(fn (AbstractModel $model, Context $context) => $context->getActor()->hasPermission('anonymous-posting.reveal')),
        ];
    }

    /**
     * @param Discussion|Post $model
     */
    protected function avatarUrl(AbstractModel $model): ?string
    {
        $avatarRules = json_decode((string)$this->settings->get('anonymous-posting.formulaireAvatars'), true);

        // Check if the setting has a value first to avoid degrading performance by retrieving a useless relationship
        // Also skip if Formulaire is not installed to avoid 500 error that would block access to the forum
        if (!$avatarRules || !class_exists(Submission::class)) {
            return null;
        }

        // Ideally we'd want to eager load this relationship, but we can't conditionally add to Flarum's eager loader at the moment
        return AnonymousAvatar::retrieve($avatarRules, $model->anonymousUserSubmissions);
    }
}
