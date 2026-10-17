<?php

namespace NYU8\FlarumEmailFilter;

use Flarum\Foundation\ValidationException;
use Flarum\Locale\Translator;
use Flarum\Settings\SettingsRepositoryInterface;
use NYU8\FlarumEmailFilter\Rule;

class EmailCustomValidator
{
    public function __construct(protected SettingsRepositoryInterface $settings, protected Translator $translator)
    {
    }

    public function validate(string $email)
    {
        $activeRules = Rule::all()->where('active', 1);
        $blacklistLiteralRules = $activeRules->where('rule_type', 3);
        $blacklisted = false;

        if (static::checkLiteral($email, $blacklistLiteralRules, false)) {
            $this->raiseBlacklistException();
        }

        $blackListRegexRules = $activeRules->where('rule_type', 4);

        if (static::checkRegex($email, $blackListRegexRules, false)) {
            $blacklisted = true;
        }

        $whiteListLiteralRules = $activeRules->where('rule_type', 1);
        $whiteListRegexRules = $activeRules->where('rule_type', 2);

        if ($blacklisted) {
            if (static::checkLiteral($email, $whiteListLiteralRules, true)) {
                return;
            } else {
                $this->raiseBlacklistException();
            }
        }

        if (count($whiteListLiteralRules) === 0 && count($whiteListRegexRules) === 0) {
            return;
        }

        if (static::checkLiteral($email, $whiteListLiteralRules, true) || static::checkRegex($email, $whiteListRegexRules, true)) {
            return;
        }

        $this->raiseWhitelistException();
    }

    private function raiseBlacklistException()
    {
        $message = $this->settings->get('nyu8-email-filter.custom_failure_message') ?: $this->translator->trans('nyu8-email-filter.forum.blacklist_exception');
        throw new ValidationException(['email' => $message]);
    }

    private function raiseWhitelistException()
    {
        $message = $this->settings->get('nyu8-email-filter.custom_failure_message') ?: $this->translator->trans('nyu8-email-filter.forum.whitelist_exception');
        throw new ValidationException(['email' => $message]);
    }

    /**
     * @param Rule[] $rules
     */
    private static function checkRegex(string $email, $rules, bool $caseSensitive = false): bool
    {
        foreach ($rules as $rule) {
            if (preg_match('/' . $rule->value . '/' . ($caseSensitive ? '' : 'i'), $email, $_match)) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param Rule[] $rules
     */
    private static function checkLiteral(string $email, $rules, bool $caseSensitive = false): bool
    {
        foreach ($rules as $rule) {
            if ($caseSensitive && $email == $rule->value) {
                return true;
            } else if (!$caseSensitive && strtolower($email) == strtolower($rule->value)) {
                return true;
            }
        }

        return false;
    }
}
