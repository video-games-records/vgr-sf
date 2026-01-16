<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartTypeRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\ScoreTools;

#[ORM\Table(name:'vgr_charttype')]
#[ORM\Entity(repositoryClass: ChartTypeRepository::class)]
class ChartType
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $name;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: false)]
    private string $mask = '';

    #[Assert\Length(max: 10)]
    #[ORM\Column(length: 10, nullable: false)]
    private string $orderBy = 'ASC';


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }



    public function setMask(string $mask): void
    {
        $this->mask = $mask;
    }

    public function getMask(): string
    {
        return $this->mask;
    }


    public function setOrderBy(string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }


    public function getNbInput(): int
    {
        return count(explode('|', $this->getMask()));
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParseMask(): array
    {
        return ScoreTools::parseChartMask($this->mask);
    }

    public function __toString()
    {
        return sprintf('%s [%s] %s (%s)', $this->name, $this->mask, $this->orderBy, $this->id);
    }
}
