<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;

trait PlayerPersonalDataTrait
{
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $presentation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $collection = null;

    #[ORM\Column(type: 'date', nullable: true)]
    protected ?DateTime $birthDate = null;

    #[ORM\Column(nullable: false, length: 1, options: ['default' => 'I'])]
    protected string $gender = 'I';

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name:'country_id', referencedColumnName:'id', nullable:true)]
    protected ?Country $country = null;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $displayPersonalInfos = false;


    public function setPresentation(?string $presentation = null): static
    {
        $this->presentation = $presentation;
        return $this;
    }

    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    public function setCollection(?string $collection = null): static
    {
        $this->collection = $collection;
        return $this;
    }

    public function getCollection(): ?string
    {
        return $this->collection;
    }

    public function setBirthDate(?DateTime $birthDate = null): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getBirthDate(): ?DateTime
    {
        return $this->birthDate;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setDisplayPersonalInfos(bool $displayPersonalInfos): static
    {
        $this->displayPersonalInfos = $displayPersonalInfos;
        return $this;
    }

    public function getDisplayPersonalInfos(): bool
    {
        return $this->displayPersonalInfos;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }
}
