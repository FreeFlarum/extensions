<?php

/*
 * This file is part of foskym/flarum-custom-levels.
 *
 * Copyright (c) 2024 FoskyM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoskyM\CustomLevels;

/**
 * An exp amount setting: a whole number ("5", "[5]") or an inclusive random range ("[1, 10]", "1, 10").
 */
final class ExpValue
{
    public const SETTINGS = [
        'foskym-custom-levels.expForPost',
        'foskym-custom-levels.expForDiscussion',
        'foskym-custom-levels.expForLike',
    ];

    // users.exp is a signed 32-bit column, one award must stay far below its limit
    public const MAX = 1000000;

    /**
     * @return array{int, int}|null inclusive [min, max], null if the value is invalid
     */
    public static function parse(mixed $value): ?array
    {
        if ($value !== null && ! is_scalar($value)) {
            return null;
        }

        // admins copy the help text's example literally (「[1, 10]」), and type full-width commas
        $value = str_replace(['「', '」', '，'], ['', '', ','], trim((string) $value));

        if ($value === '') {
            return [0, 0];
        }

        if (preg_match('/^\[(.*)\]$/s', $value, $brackets)) {
            $value = $brackets[1];
        }

        if (! preg_match('/^\s*(\d+)\s*(?:,\s*(\d+)\s*)?$/', $value, $numbers)) {
            return null;
        }

        $a = (int) $numbers[1];
        $b = isset($numbers[2]) ? (int) $numbers[2] : $a;

        if (max($a, $b) > self::MAX) {
            return null;
        }

        return [min($a, $b), max($a, $b)];
    }

    /**
     * The exp to award for a setting value, 0 if the value is invalid.
     */
    public static function roll(mixed $value): int
    {
        [$min, $max] = self::parse($value) ?? [0, 0];

        return random_int($min, $max);
    }
}
