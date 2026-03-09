<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartLibRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\ScoreTools;

#[ORM\Table(name:'vgr_player_chartlib')]
#[ORM\Entity(repositoryClass: PlayerChartLibRepository::class)]
#[ORM\UniqueConstraint(name: "uniq_player_chart", columns: ["player_chart_id", "chartlib_id"])]
class PlayerChartLib
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::BIGINT, nullable: false)]
    private string $value; // @phpstan-ignore doctrine.columnType (BIGINT is mapped to string in PHP by Doctrine)

    #[ORM\ManyToOne(targetEntity: ChartLib::class)]
    #[ORM\JoinColumn(name:'chartlib_id', referencedColumnName:'id', nullable:false)]
    private ChartLib $libChart;

    #[ORM\ManyToOne(targetEntity: PlayerChart::class, inversedBy: 'libs')]
    #[ORM\JoinColumn(name:'player_chart_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private PlayerChart $playerChart;

    /** @var array<string, mixed> */
    private array $parseValue;

    public function __toString()
    {
        return sprintf('%s', ScoreTools::formatScore($this->value, $this->getLibChart()->getType()->getMask()));
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

    public function setValue(string|int|null $value = null): static
    {
        if ($value != null) {
            $this->value = (string) $value;
        }
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setLibChart(ChartLib $libChart): static
    {
        $this->libChart = $libChart;
        return $this;
    }

    public function getLibChart(): ChartLib
    {
        return $this->libChart;
    }

    public function setPlayerChart(PlayerChart $playerChart): static
    {
        $this->playerChart = $playerChart;
        return $this;
    }

    public function getPlayerChart(): PlayerChart
    {
        return $this->playerChart;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParseValue(): array
    {
        $this->setParseValueFromValue();
        return $this->parseValue;
    }

    /**
     * @param array<string, mixed> $parseValue
     */
    public function setParseValue(array $parseValue): static
    {
        $this->parseValue = $parseValue;
        return $this;
    }


    public function setParseValueFromValue(): static
    {
        $this->parseValue = ScoreTools::getValues(
            $this->getLibChart()
                ->getType()
                ->getMask(),
            $this->value ?? null
        );
        return $this;
    }

    public function setValueFromPaseValue(): static
    {
        if ($this->parseValue == null) {
            $this->value = '';
        } else {
            $this->value = (string) ScoreTools::formToBdd(
                $this->getLibChart()
                    ->getType()
                    ->getMask(),
                $this->parseValue
            );
        }
        return $this;
    }

    public function getFormatValue(): string
    {
        return ScoreTools::formatScore(
            $this->value,
            $this->getLibChart()
                ->getType()
                ->getMask()
        );
    }
}
