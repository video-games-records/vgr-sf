<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller;

use App\BoundedContext\User\Application\Service\UserRegistrationService;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Presentation\Form\RegistrationFormType;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Register extends AbstractLocalizedController
{
    public function __construct(
        private readonly UserRegistrationService $registrationService,
        private readonly LoggerInterface $logger,
        private readonly RateLimiterFactoryInterface $registerLimiter,
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function __invoke(Request $request): Response
    {
        // Redirect authenticated users away from registration page
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $limiter = $this->registerLimiter->create($request->getClientIp());
        if (!$limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Get the plain password from the form
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPlainPassword($plainPassword);

                // Register the user (auto-enabled by default)
                $this->registrationService->registerUser($user);

                // Add success flash message
                $this->addFlash('success', 'registration.flash.success');

                // Redirect to login page
                return $this->redirectToRoute('app_login');
            } catch (Exception $e) {
                // Log the error and show a user-friendly message
                $this->logger->error('User registration failed', [
                    'exception' => $e->getMessage(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                ]);
                $this->addFlash('error', 'registration.flash.error');
            }
        }

        return $this->render('@User/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
