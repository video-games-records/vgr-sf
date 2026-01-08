<?php

namespace App\BoundedContext\User\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name:'pnu_group')]
#[ORM\Entity]
class Group
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(length: 100, unique: true, nullable: false)]
    protected string $name = '';

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    protected array $roles = [];

    public function __toString()
    {
        return sprintf('%s [%d]', $this->getName(), $this->getId());
    }

    /**
     * @param string[] $roles
     */
    public function __construct(string $name, array $roles = [])
    {
        $this->name = $name;
        $this->roles = $roles;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function removeRole(string $role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
