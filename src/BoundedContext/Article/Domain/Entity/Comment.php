<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Domain\Entity;

use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'pna_comment')]
#[ORM\Entity(repositoryClass: 'App\BoundedContext\Article\Infrastructure\Doctrine\Repository\CommentRepository')]
#[ORM\HasLifecycleCallbacks]
class Comment
{
    use TimestampableTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name:'article_id', referencedColumnName:'id', nullable:false)]
    private Article $article;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'user_id', referencedColumnName:'id', nullable:false)]
    private User $user;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $content;

    public function __toString()
    {
        return sprintf('comment [%s]', $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): void
    {
        $this->article = $article;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
