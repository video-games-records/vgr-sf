<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Story;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\Tests\BoundedContext\Article\Factory\ArticleFactory;
use App\Tests\BoundedContext\User\Story\AdminUserStory;
use Zenstruck\Foundry\Story;

final class DefaultArticleStory extends Story
{
    public function build(): void
    {
        AdminUserStory::load();

        $author = AdminUserStory::adminUser();

        // Published articles with old dates (> 30 days) and 0 views
        // so they don't interfere with trending/view-count tests
        ArticleFactory::new()
            ->published()
            ->with(['author' => $author, 'views' => 0, 'publishedAt' => new \DateTime('-60 days')])
            ->create();

        ArticleFactory::new()
            ->published()
            ->with(['author' => $author, 'views' => 0, 'publishedAt' => new \DateTime('-90 days')])
            ->create();

        ArticleFactory::new()
            ->published()
            ->with(['author' => $author, 'views' => 0, 'publishedAt' => new \DateTime('-120 days')])
            ->create();

        ArticleFactory::new()
            ->underConstruction()
            ->with(['author' => $author])
            ->create();
    }
}
