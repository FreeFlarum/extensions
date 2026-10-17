<?php

namespace NYU8\FlarumEmailFilter\Event;

use Flarum\User\Event\Saving;
use Illuminate\Support\Arr;
use NYU8\FlarumEmailFilter\EmailCustomValidator;

class UserSavingListener
{
  public function __construct(protected EmailCustomValidator $emailValidator)
  {
  }

  public function handle(Saving $event)
  {
    $email = Arr::get($event->data, 'attributes.email');

    if (!$email) {
      return;
    }

    $this->emailValidator->validate($email);
  }
}
