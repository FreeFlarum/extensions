<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api\Resource;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Flarum\Locale\TranslatorInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\DiscussionLanguage\DiscussionLanguage;
use IanM\ISO639\ISO639;
use Illuminate\Contracts\Cache\Repository as Cache;
use League\Csv\Reader;
use League\Csv\Statement;
use Rinvex\Country\CountryLoader;
use Tobyz\JsonApiServer\Context;

/**
 * @extends AbstractDatabaseResource<DiscussionLanguage>
 */
class DiscussionLanguageResource extends AbstractDatabaseResource
{
    public const CACHE_KEY = 'fof-discussion-language.languages';

    public const CSV_CACHE_KEY = 'fof-discussion-language.csv-index';

    /** @var array<string, array{english: string, native: string}>|null */
    private static ?array $csvIndex = null;

    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected ISO639 $iso,
        protected Cache $cache,
        protected TranslatorInterface $translator
    ) {
    }

    public function type(): string
    {
        return 'discussion-languages';
    }

    public function model(): string
    {
        return DiscussionLanguage::class;
    }

    public function getId(object $model, Context $context): string
    {
        return $model->code === 'any' ? 'any' : (string) $model->getKey();
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Create::make()
                ->admin(),
            Endpoint\Update::make()
                ->admin(),
            Endpoint\Delete::make()
                ->admin(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('code')
                ->requiredOnCreate()
                ->writable()
                ->minLength(2)
                ->unique('discussion_languages', 'code', true),
            Schema\Str::make('country')
                ->requiredOnCreate()
                ->writable()
                ->minLength(2),
            Schema\Str::make('name')
                ->nullable()
                ->get(fn (DiscussionLanguage $language) => $this->getLanguageName($language->code, (bool) $this->settings->get('fof-discussion-language.native'))),
            Schema\Str::make('emoji')
                ->nullable()
                ->get(function (DiscussionLanguage $language) {
                    if (!$this->settings->get('fof-discussion-language.showFlags')) {
                        return null;
                    }

                    try {
                        return CountryLoader::country($language->country)->getEmoji();
                    } catch (\Throwable $ignored) {
                        return null;
                    }
                }),
        ];
    }

    public function saved(object $model, Context $context): ?object
    {
        $this->cache->forget(self::CACHE_KEY);

        return $model;
    }

    public function deleted(object $model, Context $context): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }

    protected function getLanguageName(string $code, bool $native): ?string
    {
        if ($code === 'any') {
            return $this->translator->trans('fof-discussion-language.forum.index_language.any');
        }

        $name = ucfirst(
            $native
                ? $this->iso->nativeByCode1($code)
                : $this->iso->languageByCode1($code)
        );

        if ($name) {
            return $name;
        }

        // Fallback to cached CSV index for codes not in ISO 639-1
        if (self::$csvIndex === null) {
            self::$csvIndex = $this->cache->rememberForever(self::CSV_CACHE_KEY, function () {
                return self::buildCsvIndex();
            });
        }

        $entry = self::$csvIndex[$code] ?? null;

        return $entry ? ($native ? $entry['native'] : $entry['english']) : null;
    }

    private static function buildCsvIndex(): array
    {
        $index = [];
        $csv = Reader::from(__DIR__.'/../../../resources/wikipedia-iso-639-2-codes.csv');
        $csv->setHeaderOffset(0);

        foreach ((new Statement())->process($csv) as $record) {
            $entry = [
                'english' => $record['Language name(s)'],
                'native'  => $record['Native name(s)'] ?: $record['Language name(s)'],
            ];

            foreach (['639-1', '639-2', '639-3'] as $col) {
                if (!empty($record[$col])) {
                    $index[$record[$col]] = $entry;
                }
            }
        }

        return $index;
    }
}
