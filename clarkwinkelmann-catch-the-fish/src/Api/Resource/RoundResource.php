<?php

namespace ClarkWinkelmann\CatchTheFish\Api\Resource;

use ClarkWinkelmann\CatchTheFish\Repositories\FishRepository;
use ClarkWinkelmann\CatchTheFish\Repositories\RankingRepository;
use ClarkWinkelmann\CatchTheFish\Repositories\RoundRepository;
use ClarkWinkelmann\CatchTheFish\Round;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Flarum\Api\Serializer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Tobyz\JsonApiServer\Context as OriginalContext;
use function Tobyz\JsonApiServer\json_api_response;

/**
 * @extends AbstractDatabaseResource<Round>
 */
class RoundResource extends AbstractDatabaseResource
{
    public function __construct(
        protected RoundRepository $rounds,
        protected FishRepository $fishes,
        protected RankingRepository $rankings
    )
    {
    }

    public function type(): string
    {
        return 'catchthefish-rounds';
    }

    public function model(): string
    {
        return Round::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->before(function (Context $context) {
                    $context->getActor()->assertCan('list', Round::class);
                }),
            Endpoint\Endpoint::make('create')
                ->route('POST', '/')
                ->action(function (Context $context) {
                    $context->getActor()->assertCan('create', Round::class);

                    return $this->rounds->store(Arr::get($context->body(), 'data.attributes', []));
                })
                ->response(function (Context $context, Round $round) {
                    return $this->serialize($context, $this, [$round], [], true)->withStatus(201);
                }),
            Endpoint\Show::make()
                ->can('view'),
            Endpoint\Endpoint::make('update')
                ->route('PATCH', '/{id}')
                ->can('update')
                ->action(function (Context $context) {
                    return $this->rounds->update($context->model, Arr::get($context->body(), 'data.attributes', []));
                }),
            Endpoint\Delete::make()
                ->can('delete'),
            Endpoint\Endpoint::make('fishes.index')
                ->route('GET', '/{id}/fishes')
                ->can('listFishes')
                ->action(function (Context $context) {
                    return $this->fishes->all($context->model)->all();
                })
                ->response(function (Context $context, array $fishes) {
                    return $this->serialize($context, $context->resource('catchthefish-fishes'), $fishes, [
                        'lastUserPlacement' => [],
                        'lastUserNaming' => [],
                        'placementModel' => [
                            'discussion' => [], // For when it's a post
                        ],
                    ]);
                }),
            Endpoint\Endpoint::make('fishes.create')
                ->route('POST', '/{id}/fishes')
                ->can('createFish')
                ->action(function (Context $context) {
                    return $this->fishes->store($context->model, Arr::get($context->body(), 'data.attributes', []));
                })
                ->response(function (Context $context, $fish) {
                    return $this->serialize($context, $context->resource('catchthefish-fishes'), [$fish], [], true)->withStatus(201);
                }),
            Endpoint\Endpoint::make('fishes.images')
                ->route('POST', '/{id}/fishes-from-images')
                ->can('createFish')
                ->action(function (Context $context) {
                    $files = array_filter($context->request->getUploadedFiles(), function (string $key) {
                        return Str::startsWith($key, 'image');
                    }, ARRAY_FILTER_USE_KEY);

                    return $this->fishes->bulkImageImport($context->model, $files);
                })
                ->response(function (Context $context, array $fishes) {
                    return $this->serialize($context, $context->resource('catchthefish-fishes'), $fishes, []);
                }),
            Endpoint\Endpoint::make('rankings.index')
                ->route('GET', '/{id}/rankings')
                ->can('listRankings')
                ->action(function (Context $context) {
                    return $this->rankings->all($context->model)->all();
                })
                ->response(function (Context $context, array $rankings) {
                    return $this->serialize($context, $context->resource('catchthefish-rankings'), $rankings, [
                        'user' => [],
                    ]);
                }),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('name'),
            Schema\DateTime::make('starts_at'),
            Schema\DateTime::make('ends_at'),
            Schema\Integer::make('my_catch_count')
                ->get(function (Round $round, Context $context) {
                    $ranking = $round->userRanking($context->getActor());

                    return $ranking ? $ranking->catch_count : 0;
                }),
            Schema\Relationship\ToOne::make('myRanking')
                ->type('catchthefish-rankings')
                ->includable()
                ->get(function (Round $round, Context $context) {
                    return $round->userRanking($context->getActor());
                }),
        ];
    }

    /**
     * @param Round $model
     */
    public function delete(object $model, OriginalContext $context): void
    {
        $this->rounds->delete($model);
    }

    protected function serialize(Context $context, $resource, array $models, array $include, bool $single = false): ResponseInterface
    {
        $serializer = new Serializer($context);

        foreach ($models as $model) {
            $serializer->addPrimary($resource, $model, $include);
        }

        [$data, $included] = $serializer->serialize();

        $document = ['data' => $single ? $data[0] : $data];

        if (count($included)) {
            $document['included'] = $included;
        }

        return json_api_response($document);
    }
}
