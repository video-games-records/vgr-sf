<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Factory;

use App\BoundedContext\Article\Domain\Entity\Article;
use App\BoundedContext\Article\Domain\ValueObject\ArticleStatus;
use App\Tests\BoundedContext\User\Factory\UserFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Article>
 */
final class ArticleFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Article::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array|callable
    {
        return [
            'author' => UserFactory::new(),
            'slug' => self::faker()->slug(),
            'status' => ArticleStatus::PUBLISHED,
            'nbComment' => 0,
            'views' => 0,
            'publishedAt' => new \DateTime(),
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Article $article): void {
            if ($article->getTranslations()->isEmpty()) {
                $article->setTitle(self::faker()->sentence(5), 'en');
                $article->setContent(self::faker()->text(500), 'en');
            }
        });
    }

    public function published(): static
    {
        return $this->with([
            'status' => ArticleStatus::PUBLISHED,
            'publishedAt' => new \DateTime(),
        ]);
    }

    public function underConstruction(): static
    {
        return $this->with([
            'status' => ArticleStatus::UNDER_CONSTRUCTION,
            'publishedAt' => null,
        ]);
    }
}
