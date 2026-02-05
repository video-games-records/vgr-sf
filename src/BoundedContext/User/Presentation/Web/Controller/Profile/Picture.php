<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Presentation\Form\AvatarUploadType;
use App\SharedKernel\Infrastructure\FileSystem\Manager\AvatarManager;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
class Picture extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AvatarManager $avatarManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/profile/picture', name: 'app_profile_picture', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(AvatarUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('avatar')->getData();

            $extension = $this->avatarManager->getExtension($file->getMimeType() ?? 'image/png');
            $filename = $user->getId() . '_' . uniqid() . '.' . $extension;

            // Resize to 100x100
            $manager = ImageManager::gd();
            $image = $manager->read($file->getContent());
            $image->cover(100, 100);

            $encoder = match ($extension) {
                'jpg' => new JpegEncoder(quality: 90),
                default => new PngEncoder(),
            };

            $this->avatarManager->write('users/' . $filename, (string) $image->encode($encoder));

            $user->setAvatar($filename);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('profile.picture.message.success', [], 'User'));

            return $this->redirectToRoute('app_profile_picture');
        }

        return $this->render('@User/profile/picture.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
