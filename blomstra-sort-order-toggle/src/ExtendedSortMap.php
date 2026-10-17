<?php

namespace Blomstra\SortOrderToggle;

use Flarum\Api\Resource\DiscussionResource;
use Illuminate\Support\Str;

class ExtendedSortMap
{
    public function __construct(protected DiscussionResource $discussions)
    {
    }

    public function __invoke(): array
    {
        $map = $this->discussions->sortMap();

        // Scan the sort map for entries without opposites, and create one with a minus sign in front
        $reverseMap = [];

        foreach ($map as $frontendSort => $apiSort) {
            $reverseMap[trim($apiSort, '-')][] = $frontendSort;
        }

        foreach ($reverseMap as $apiField => $frontendSorts) {
            if (count($frontendSorts) === 1) {
                $existingFrontendSort = $frontendSorts[0];
                $existingApiSort = $map[$existingFrontendSort];

                $map["-$existingFrontendSort"] = Str::startsWith($existingApiSort, '-') ? $apiField : "-$existingApiSort";
            }
        }

        return $map;
    }
}
