<?php

namespace ClarkWinkelmann\ComposerPage\Content;

use Flarum\Extension\ExtensionManager;
use Flarum\Frontend\Document;
use Flarum\Tags\Content\Tag;
use Psr\Http\Message\ServerRequestInterface;

class TagComposer
{
    public function __construct(protected ExtensionManager $manager)
    {
    }

    public function __invoke(Document $document, ServerRequestInterface $request)
    {
        if (!$this->manager->isEnabled('flarum-tags')) {
            return;
        }

        // Invoke the original tag content class, that way any required tag will be loaded without the need for special code
        $content = resolve(Tag::class);

        $content($document, $request);
    }
}
