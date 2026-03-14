<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller;

use League\Flysystem\FilesystemException;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Infrastructure\FileSystem\Manager\AvatarManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
class GetUserAvatar extends AbstractController
{
    public function __construct(private readonly AvatarManager $avatarManager)
    {
    }

    /**
     * @throws FilesystemException
     */
    #[Route('/{id}/avatar', name: 'pn_user_avatar_show', requirements: ['page' => '\d+'], stateless: false)]
    public function download(User $user): StreamedResponse
    {
        return $this->avatarManager->read('user/' . $user->getAvatar());
    }
}
