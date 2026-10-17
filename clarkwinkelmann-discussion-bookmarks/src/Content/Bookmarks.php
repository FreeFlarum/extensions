<?php

namespace ClarkWinkelmann\DiscussionBookmarks\Content;

use Flarum\Api\Client;
use Flarum\Api\Resource\DiscussionResource;
use Flarum\Frontend\Document;
use Flarum\Locale\TranslatorInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class Bookmarks
{
    public function __construct(protected Client $api, protected Factory $view, protected TranslatorInterface $translator, protected DiscussionResource $resource)
    {
    }

    public function __invoke(Document $document, ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();

        $sort = Arr::pull($queryParams, 'sort');
        $q = Arr::pull($queryParams, 'q');
        $page = max(1, intval(Arr::pull($queryParams, 'page')));

        $sortMap = $this->resource->sortMap();

        $params = [
            'sort' => $sort && isset($sortMap[$sort]) ? $sortMap[$sort] : null,
            'filter' => [
                'bookmarked' => '1',
            ],
            'page' => ['number' => $page],
        ];

        if ($q) {
            $params['filter']['q'] = $q;
        }

        $apiDocument = $this->getApiDocument($request, $params);

        $document->title = $this->translator->trans('clarkwinkelmann-discussion-bookmarks.forum.page.title');
        $document->content = $this->view->make('flarum.forum::frontend.content.index', compact('apiDocument', 'page'));
        $document->payload['apiDocument'] = $apiDocument;

        return $document;
    }

    private function getApiDocument(ServerRequestInterface $request, array $params)
    {
        return json_decode($this->api->withParentRequest($request)->withQueryParams($params)->get('/discussions')->getBody());
    }
}
