<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Domain\Contracts;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Genre;
use Doctrine\Common\Collections\Collection;
use DateTime;

interface GameInfoInterface
{
    public function getName(): string;
    public function getSlug(): string;
    /**
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection;
    public function getReleaseDate(): ?DateTime;
    public function getSummary(): ?string;
    public function getStoryline(): ?string;
    public function getUrl(): ?string;
}
