<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\ResetPassword;

use App\BoundedContext\User\Application\Service\SecurityHistoryManager;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Presentation\Form\ResetPasswordType;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class ResetPassword extends AbstractLocalizedController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SecurityHistoryManager $securityHistoryManager,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/reset-password/confirm/{token}', name: 'app_reset_password_confirm', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, string $token): Response
    {
        // Redirect authenticated users
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);

        // Invalid or expired token
        if (null === $user || !$user->getConfirmationToken()) {
            $this->addFlash(
                'error',
                $this->translator->trans('reset_password.flash.invalid_token', [], 'User')
            );
            return $this->redirectToRoute('app_reset_password_request');
        }

        // Check if the token has expired (e.g., 24 hours)
        if ($user->getPasswordRequestedAt() && $user->isPasswordRequestExpired(86400)) {
            $this->addFlash(
                'error',
                $this->translator->trans('reset_password.flash.expired_token', [], 'User')
            );
            return $this->redirectToRoute('app_reset_password_request');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPlainPassword($plainPassword);
                $user->setConfirmationToken(null);
                $user->setPasswordRequestedAt(null);

                $this->em->flush();

                // Log security event
                $this->securityHistoryManager->recordEvent($user, SecurityEventTypeEnum::PASSWORD_RESET_COMPLETE, [
                    'email' => $user->getEmail()
                ]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('reset_password.flash.success', [], 'User')
                );

                return $this->redirectToRoute('app_login');
            } catch (Exception $e) {
                $this->logger->error('Password reset failed', [
                    'exception' => $e->getMessage(),
                    'user_id' => $user->getId(),
                ]);
                $this->addFlash(
                    'error',
                    $this->translator->trans('reset_password.flash.error', [], 'User')
                );
            }
        }

        return $this->render('@User/reset_password/confirm.html.twig', [
            'form' => $form,
        ]);
    }
}
