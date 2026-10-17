<?php

namespace ImDong\SpamAlert\Notification;


use Flarum\Notification\AlertableInterface;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Post\Post;
use Flarum\User\User;

class PostSpamHidedBlueprint implements BlueprintInterface, AlertableInterface
{
    public function __construct(public Post $post)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject(): ?\Flarum\Database\AbstractModel
    {
        return $this->post;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromUser(): ?\Flarum\User\User
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): mixed
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return 'postSpamHide';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubjectModel(): string
    {
        return Post::class;
    }

}
