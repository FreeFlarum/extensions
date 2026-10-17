<?php

namespace Justoverclock\Feedback\Validator;

use Flarum\Foundation\AbstractValidator;

class FeedbackValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    protected array $rules = [
        'message' => ['required', 'string'],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getMessages(): array
    {
        $error = resolve('translator')->trans('justoverclock-feedback.api.validation_error');

        return [
            'required' => $error,
        ];
    }
}