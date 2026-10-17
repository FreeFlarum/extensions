<?php

namespace Justoverclock\Feedback\Api\Controller;

use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Arr;
use Justoverclock\Feedback\Validator\FeedbackValidator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubmitFeedbackController implements RequestHandlerInterface
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected TranslatorInterface $translator,
        protected Mailer $mailer,
        protected FeedbackValidator $validator
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $data = $request->getParsedBody();

        $feedback = [
            'feedbackType' => Arr::get($data, 'feedbackType'),
            'email' => Arr::get($data, 'email'),
            'message' => Arr::get($data, 'message'),
            'url' => Arr::get($data, 'url'),
        ];

        $this->validator->assertValid(['message' => $feedback['message']]);

        $this->sendEmail($feedback, $actor);

        return new JsonResponse([
            'data' => [
                'type' => 'feedback',
                'id' => 'feedback',
                'attributes' => Arr::only($feedback, ['email', 'feedbackType', 'message']),
            ],
        ], 201);
    }

    private function sendEmail(array $feedback, User $actor): void
    {
        $subject = $this->translator->trans('justoverclock-feedback.forum.email.subject');
        $body = $this->translator->trans('justoverclock-feedback.forum.email.body', [
            'type'    => $feedback['feedbackType'],
            'message' => $feedback['message'],
            'email'   => $feedback['email'],
            'url'     => $feedback['url'],
            'user'    => $actor->isGuest() ? $this->translator->trans('justoverclock-feedback.forum.email.guest') : $actor->display_name
        ]);

        $recipient = $this->settings->get('justoverclock-feedback.ContactMail');

        if (! empty($recipient)) {
            $this->mailer->raw($body, function (Message $message) use ($subject, $recipient) {
                $message->to($recipient)->subject($subject);
            });
        }
    }
}
