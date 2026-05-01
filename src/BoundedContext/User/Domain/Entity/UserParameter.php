<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Domain\Entity;

use App\BoundedContext\User\Domain\ValueObject\UserParameterKeyEnum;
use App\BoundedContext\User\Infrastructure\Persistence\Doctrine\UserParameterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'pnu_user_parameter')]
#[ORM\UniqueConstraint(name: 'unique_user_param', columns: ['user_id', 'param_key'])]
#[ORM\Entity(repositoryClass: UserParameterRepository::class)]
class UserParameter
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(name: 'param_key', length: 50, enumType: UserParameterKeyEnum::class)]
    private UserParameterKeyEnum $paramKey;

    #[ORM\Column(length: 50)]
    private string $value;

    public function __construct(User $user, UserParameterKeyEnum $paramKey, string $value)
    {
        $this->user = $user;
        $this->paramKey = $paramKey;
        $this->value = $value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getParamKey(): UserParameterKeyEnum
    {
        return $this->paramKey;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
