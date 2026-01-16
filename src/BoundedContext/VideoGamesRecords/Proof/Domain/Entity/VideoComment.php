<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\VideoCommentRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player\PlayerTrait;

#[ORM\Table(name:'vgr_video_comment')]
#[ORM\Entity(repositoryClass: VideoCommentRepository::class)]
class VideoComment
{
    use TimestampableEntity;
    use PlayerTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Video::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name:'video_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Video $video;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $content;

    public function __toString()
    {
        return sprintf('comment [%s]', $this->id);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function setVideo(Video $video): void
    {
        $this->video = $video;
    }


    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
