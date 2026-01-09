<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Event\PasswordChangedEvent;
use App\BoundedContext\User\Presentation\Form\ChangePasswordType;
use App\SharedKernel\Domain\Interface\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class ChangePassword extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route('/profile/password', name: 'app_profile_password', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the new password from the form
            $newPassword = $form->get('newPassword')->getData();

            // Hash and set the new password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            // Save changes
            $this->entityManager->flush();

            // Dispatch password changed event
            $passwordChangedEvent = new PasswordChangedEvent($user);
            $this->eventDispatcher->dispatch($passwordChangedEvent);

            // Add flash message and redirect (Post-Redirect-Get pattern)
            $this->addFlash('success', 'Your password has been changed successfully!');

            return $this->redirectToRoute('app_profile_password');
        }

        // Render the form (GET or POST with errors)
        return $this->render('@User/profile/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
