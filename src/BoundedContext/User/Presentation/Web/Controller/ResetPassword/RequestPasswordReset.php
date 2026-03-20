<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\ResetPassword;

use App\BoundedContext\User\Application\Service\SecurityHistoryManager;
use App\BoundedContext\User\Application\Service\UserManager;
use App\BoundedContext\User\Presentation\Form\RequestPasswordResetType;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use App\SharedKernel\Infrastructure\TokenGenerator;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class RequestPasswordReset extends AbstractLocalizedController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly TokenGenerator $tokenGenerator,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly SecurityHistoryManager $securityHistoryManager,
        private readonly LoggerInterface $logger,
        private readonly int $retryTtl = 7200
    ) {
    }

    #[Route('/reset-password/request', name: 'app_reset_password_request', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        // Redirect authenticated users
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(RequestPasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            try {
                $user = $this->userManager->findUserByUsernameOrEmail($email);

                if ($user && (null === $user->getPasswordRequestedAt() || $user->isPasswordRequestExpired($this->retryTtl))) {
                    $user->setConfirmationToken($this->tokenGenerator->generateToken());

                    // Generate reset URL
                    $resetUrl = $this->generateUrl('app_reset_password_confirm', [
                        'token' => $user->getConfirmationToken(),
                        '_locale' => $request->getLocale()
                    ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

                    $body = sprintf(
                        $this->translator->trans('password_reset.message', [], 'UserEmail', $user->getLanguage()),
                        $user->getUsername(),
                        $resetUrl
                    );

                    $emailMessage = (new Email())
                        ->to($user->getEmail())
                        ->subject($this->translator->trans('password_reset.subject', [], 'UserEmail', $user->getLanguage()))
                        ->text($body)
                        ->html($body);

                    $this->mailer->send($emailMessage);

                    $user->setPasswordRequestedAt(new DateTime());
                    $this->userManager->updateUser($user);

                    $this->securityHistoryManager->recordEvent($user, SecurityEventTypeEnum::PASSWORD_RESET_REQUEST, [
                        'email' => $user->getEmail()
                    ]);
                }

                // Always show success message to prevent email enumeration
                $this->addFlash(
                    'success',
                    $this->translator->trans('reset_password.flash.request_success', [], 'User')
                );

                return $this->redirectToRoute('app_login');
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('Password reset email failed', [
                    'exception' => $e->getMessage(),
                    'email' => $email,
                ]);
                $this->addFlash(
                    'error',
                    $this->translator->trans('reset_password.flash.request_error', [], 'User')
                );
            } catch (Exception $e) {
                $this->logger->error('Password reset request failed', [
                    'exception' => $e->getMessage(),
                    'email' => $email,
                ]);
                $this->addFlash(
                    'error',
                    $this->translator->trans('reset_password.flash.request_error', [], 'User')
                );
            }
        }

        return $this->render('@User/reset_password/request.html.twig', [
            'form' => $form,
        ]);
    }
}
