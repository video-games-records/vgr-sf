<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[IsGranted('ROLE_USER')]
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class TwoFactorAuth extends AbstractController
{
    public function __construct(
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/profile/security', name: 'app_profile_security', methods: ['GET'])]
    public function show(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $qrContent = null;
        if ($user->getTotpSecret() !== null && !$user->isTotpEnabled()) {
            $qrContent = $this->totpAuthenticator->getQRContent($user);
        }

        return $this->render('@User/profile/security.html.twig', [
            'qr_content' => $qrContent,
            'totp_enabled' => $user->isTotpAuthenticationEnabled(),
            'totp_pending' => $user->getTotpSecret() !== null && !$user->isTotpEnabled(),
        ]);
    }

    #[Route('/profile/security/enable', name: 'app_profile_security_enable', methods: ['POST'])]
    public function enable(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->validateCsrfOrThrow($request, 'totp_enable');

        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isTotpAuthenticationEnabled()) {
            $secret = $this->totpAuthenticator->generateSecret();
            $user->setTotpSecret($secret);
            $user->setTotpEnabled(false);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_profile_security');
    }

    #[Route('/profile/security/confirm', name: 'app_profile_security_confirm', methods: ['POST'])]
    public function confirm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->validateCsrfOrThrow($request, 'totp_confirm');

        /** @var User $user */
        $user = $this->getUser();

        $code = (string) $request->request->get('totp_code', '');

        if ($user->getTotpSecret() === null) {
            $this->addFlash('danger', $this->translator->trans('profile.security.flash.no_secret', [], 'User'));
            return $this->redirectToRoute('app_profile_security');
        }

        $user->setTotpEnabled(true);
        if (!$this->totpAuthenticator->checkCode($user, $code)) {
            $user->setTotpEnabled(false);
            $this->addFlash('danger', $this->translator->trans('profile.security.flash.invalid_code', [], 'User'));
            return $this->redirectToRoute('app_profile_security');
        }

        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('profile.security.flash.enabled', [], 'User'));

        return $this->redirectToRoute('app_profile_security');
    }

    #[Route('/profile/security/disable', name: 'app_profile_security_disable', methods: ['POST'])]
    public function disable(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->validateCsrfOrThrow($request, 'totp_disable');

        /** @var User $user */
        $user = $this->getUser();

        $password = (string) $request->request->get('current_password', '');

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            $this->addFlash('danger', $this->translator->trans('profile.security.flash.invalid_password', [], 'User'));
            return $this->redirectToRoute('app_profile_security');
        }

        $user->setTotpSecret(null);
        $user->setTotpEnabled(false);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('profile.security.flash.disabled', [], 'User'));

        return $this->redirectToRoute('app_profile_security');
    }

    private function validateCsrfOrThrow(Request $request, string $intention): void
    {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid($intention, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
