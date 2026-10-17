<?php

namespace Nearata\EmbedVideo\Command;

use Flarum\Console\AbstractCommand;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Symfony\Component\Console\Command\Command;
use Illuminate\Database\Eloquent\Collection;
use s9e\TextFormatter\Utils;

class PurgeCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('nearataEmbedVideo:purge');
    }

    protected function fire(): int
    {
        $this->info('Cleaning posts...');

        Post::chunk(50, function (Collection $items) {
            foreach ($items as $i) {
                if (! ($i instanceof CommentPost)) {
                    continue;
                }

                $i->parsed_content = Utils::removeTag($i->parsed_content, 'EMBED-VIDEO');
                $i->save();
            }
        });

        $this->info('Operation complete...');

        return Command::SUCCESS;
    }
}
