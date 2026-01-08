<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Domain\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\SharedKernel\Domain\Entity\TimestampableTrait;
use App\BoundedContext\User\Infrastructure\Persistence\Doctrine\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'pnu_user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[DoctrineAssert\UniqueEntity(["email"])]
#[DoctrineAssert\UniqueEntity(["username"])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(length: 100, unique: true, nullable: false)]
    protected string $username = '';

    #[ORM\Column(length: 180, unique: true, nullable: false)]
    private string $email = '';

    #[ORM\Column(nullable: false, options: ['default' => true])]
    protected bool $enabled = true;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: 'json', nullable: false)]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     */
    #[Assert\NotBlank(groups: ['user:create'])]
    protected ?string $plainPassword = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $lastLogin = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    protected ?string $confirmationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $passwordRequestedAt = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    protected int $nbConnexion = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    protected int $nbForumMessage = 0;

    #[ORM\Column(length: 255, nullable: false, options: ['default' => 'default.png'])]
    protected string $avatar = 'default.png';

    #[ORM\Column(length: 1000, nullable: true)]
    protected ?string $comment = null;

    #[ORM\Column(length: 2, nullable: false, options: ['default' => 'en'])]
    protected string $language = 'en';

    #[ORM\Column(length: 128)]
    #[Gedmo\Slug(fields: ['username'])]
    protected string $slug;

    /** @var Collection<int, Group> */
    #[ORM\JoinTable(name: 'pnu_user_group')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Group::class)]
    protected Collection $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setEnabled(bool $boolean): void
    {
        $this->enabled = (bool) $boolean;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTime $time = null): void
    {
        if ($time === null) {
            $time = new \DateTime();
        }

        $lastLogin = $this->getLastLogin();

        if ($lastLogin === null || $lastLogin->format('Y-m-d') !== $time->format('Y-m-d')) {
            ++$this->nbConnexion;
        }

        $this->lastLogin = $time;
    }

    public function updateLastLoginOnly(?\DateTime $time = null): void
    {
        if ($time === null) {
            $time = new \DateTime();
        }

        $this->lastLogin = $time;
    }

    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setPasswordRequestedAt(?DateTime $date = null): void
    {
        $this->passwordRequestedAt = $date;
    }

    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl < time();
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getNbConnexion(): int
    {
        return $this->nbConnexion;
    }

    public function setNbConnexion(int $nbConnexion): void
    {
        $this->nbConnexion = $nbConnexion;
    }

    public function getNbForumMessage(): int
    {
        return $this->nbForumMessage;
    }

    public function setNbForumMessage(int $nbForumMessage): void
    {
        $this->nbForumMessage = $nbForumMessage;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment = null): void
    {
        $this->comment = $comment;
    }

    /**
     * @param Collection<int, Group> $groups
     */
    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setPlainPassword(?string $password): void
    {
        $this->plainPassword = $password;
    }

    public function addGroup(Group $group): void
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }
    }

    public function removeGroup(Group $group): void
    {
        if (!isset($this->groups)) {
            $this->groups = new ArrayCollection();
        }
        $this->groups->removeElement($group);
    }

    public function __toString()
    {
        return sprintf('%s [%d]', $this->getUsername(), $this->getId());
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }

    public function removeRole(string $role): void
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
