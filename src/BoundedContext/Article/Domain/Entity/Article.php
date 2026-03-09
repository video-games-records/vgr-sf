<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Domain\Entity;

use App\BoundedContext\Article\Domain\ValueObject\ArticleStatus;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Entity\TimestampableTrait;
use App\SharedKernel\Domain\Entity\TranslatableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Table(name:'pna_article')]
#[ORM\Entity(repositoryClass: 'App\BoundedContext\Article\Infrastructure\Doctrine\Repository\ArticleRepository')]
#[ORM\HasLifecycleCallbacks]
class Article
{
    use TimestampableTrait;
    use TranslatableTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: ArticleStatus::class)]
    private ArticleStatus $status = ArticleStatus::UNDER_CONSTRUCTION;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbComment = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $views = 0;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'author_id', referencedColumnName:'id', nullable:false)]
    private User $author;

    #[ORM\Column(nullable: true)]
    private ?DateTime $publishedAt = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class)]
    private Collection $comments;

    /** @var Collection<string, ArticleTranslation> */
    #[ORM\OneToMany(
        mappedBy: 'translatable',
        targetEntity: ArticleTranslation::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'locale'
    )]
    private Collection $translations;

    #[ORM\Column(length: 255, unique: false)]
    private string $slug;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getDefaultTitle(), $this->id);
    }

    public function getDefaultTitle(): string
    {
        return $this->getTitle('en') ?: 'Untitled';
    }

    public function getDefaultContent(): string
    {
        return $this->getContent('en') ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStatus(ArticleStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function getArticleStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function setNbComment(int $nbComment): static
    {
        $this->nbComment = $nbComment;
        return $this;
    }

    public function getNbComment(): int
    {
        return $this->nbComment;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;
        return $this;
    }

    public function incrementViews(): void
    {
        $this->views++;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTime $publishedAt = null): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @param Collection<int, Comment> $comments
     */
    public function setComments(Collection $comments): static
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return Collection<string, ArticleTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param Collection<string, ArticleTranslation> $translations
     */
    public function setTranslations(Collection $translations): static
    {
        $this->translations = $translations;
        return $this;
    }


    protected function hasTranslationContent(object $translation): bool
    {
        /** @var ArticleTranslation $translation  */
        return !empty($translation->getTitle()) || !empty($translation->getContent());
    }

    protected function setTranslatableOnTranslation(object $translation): void
    {
        /** @var ArticleTranslation $translation  */
        $translation->setTranslatable($this);
    }

    protected function getTranslationLocale(object $translation): string
    {
        /** @var ArticleTranslation $translation  */
        return $translation->getLocale();
    }


    public function setTitle(string $title, ?string $locale = null): static
    {
        $locale = $locale ?: $this->getCurrentLocale() ?: 'en';

        if (!$this->translations->containsKey($locale)) {
            $translation = new ArticleTranslation();
            $translation->setTranslatable($this);
            $translation->setLocale($locale);
            $this->translations->set($locale, $translation);
        }

        /** @var ArticleTranslation $translation  */
        $translation = $this->translations->get($locale);
        $translation->setTitle($title);
        return $this;
    }

    public function getTitle(?string $locale = null): ?string
    {
        $translation = $this->translate($locale);
        /** @var ArticleTranslation|null $translation  */
        return $translation?->getTitle();
    }

    public function setContent(string $content, ?string $locale = null): static
    {
        $locale = $locale ?: $this->getCurrentLocale() ?: 'en';

        if (!$this->translations->containsKey($locale)) {
            $translation = new ArticleTranslation();
            $translation->setTranslatable($this);
            $translation->setLocale($locale);
            $this->translations->set($locale, $translation);
        }

        /** @var ArticleTranslation $translation  */
        $translation = $this->translations->get($locale);
        $translation->setContent($content);
        return $this;
    }

    public function getContent(?string $locale = null): ?string
    {
        /** @var ArticleTranslation|null $translation  */
        $translation = $this->translate($locale);
        return $translation?->getContent();
    }

    public function mergeNewTranslations(): void
    {
        // Not needed anymore
    }
}
