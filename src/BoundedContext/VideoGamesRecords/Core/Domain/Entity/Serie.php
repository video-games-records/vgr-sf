<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\SerieRepository;
use App\SharedKernel\Domain\Traits\Accessor\CurrentLocale;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\SerieBadge;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPlayerTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbTeamTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PictureTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\SerieStatus;

#[ORM\Table(name:'vgr_serie')]
#[ORM\Entity(repositoryClass: SerieRepository::class)]
class Serie
{
    use TimestampableEntity;
    use NbChartTrait;
    use NbGameTrait;
    use PictureTrait;
    use NbPlayerTrait;
    use NbTeamTrait;
    use CurrentLocale;

    private const string DEFAULT_LOCALE = 'en';

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(name: 'libSerie', length: 255, nullable: false)]
    private string $libSerie;

    #[ORM\Column(nullable: false)]
    private string $status = SerieStatus::INACTIVE;


    #[ORM\Column(length: 128)]
    #[Gedmo\Slug(fields: ['libSerie'])]
    protected string $slug;

    /**
     * @var Collection<int, Game>
     */
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'serie')]
    private Collection $games;


    #[ORM\OneToOne(targetEntity: SerieBadge::class, cascade: ['persist', 'remove'], inversedBy: 'serie')]
    #[ORM\JoinColumn(name:'badge_id', referencedColumnName:'id', nullable:true, onDelete: 'SET NULL')]
    private ?SerieBadge $badge = null;

    /**
     * @var Collection<string, SerieTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: SerieTranslation::class,
        mappedBy: 'translatable',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'locale'
    )]
    private Collection $translations;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s [%s]', $this->getDefaultName(), $this->id);
    }

    public function getDefaultName(): string
    {
        return $this->libSerie;
    }

    public function getName(): string
    {
        return $this->libSerie;
    }

    public function setLibSerie(string $libSerie): static
    {
        $this->libSerie = $libSerie;
        return $this;
    }

    public function getLibSerie(): string
    {
        return $this->libSerie;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSerieStatus(): SerieStatus
    {
        return new SerieStatus($this->status);
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function setBadge(?SerieBadge $badge = null): static
    {
        $this->badge = $badge;
        return $this;
    }

    public function getBadge(): ?SerieBadge
    {
        return $this->badge;
    }

    /**
     * @return Collection<string, SerieTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param Collection<string, SerieTranslation> $translations
     */
    public function setTranslations(Collection $translations): static
    {
        $this->translations = $translations;
        return $this;
    }

    public function addTranslation(SerieTranslation $translation): void
    {
        if (!$this->translations->contains($translation)) {
            $translation->setTranslatable($this);
            $this->translations->set($translation->getLocale(), $translation);
        }
    }

    public function removeTranslation(SerieTranslation $translation): void
    {
        $this->translations->removeElement($translation);
    }

    public function translate(?string $locale = null, bool $fallbackToDefault = true): ?SerieTranslation
    {
        $locale = $locale ?: $this->currentLocale ?: self::DEFAULT_LOCALE;

        // If translation exists for requested locale
        if ($this->translations->containsKey($locale)) {
            return $this->translations->get($locale);
        }

        // Fallback to default locale if enabled and different from requested locale
        if (
            $fallbackToDefault
            && $locale !== self::DEFAULT_LOCALE
            && $this->translations->containsKey(self::DEFAULT_LOCALE)
        ) {
            return $this->translations->get(self::DEFAULT_LOCALE);
        }

        // Last resort: return first translation even if empty
        return $this->translations->first() ?: null;
    }

    /**
     * @return array<string>
     */
    public function getAvailableLocales(): array
    {
        return $this->translations->getKeys();
    }

    public function setDescription(string $description, ?string $locale = null): static
    {
        $locale = $locale ?: $this->currentLocale ?: self::DEFAULT_LOCALE;

        if (!$this->translations->containsKey($locale)) {
            $translation = new SerieTranslation();
            $translation->setTranslatable($this);
            $translation->setLocale($locale);
            $this->translations->set($locale, $translation);
        }

        $this->translations->get($locale)?->setDescription($description);
        return $this;
    }

    public function getDescription(?string $locale = null): ?string
    {
        $translation = $this->translate($locale);
        return $translation?->getDescription();
    }
}
