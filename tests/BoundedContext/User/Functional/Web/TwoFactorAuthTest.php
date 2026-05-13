<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Functional\Web;

use App\BoundedContext\User\Domain\Entity\User;
use App\Tests\BoundedContext\User\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class TwoFactorAuthTest extends AbstractWebFunctionalTestCase
{
    // ------------------------------------------------------------------
    // GET /en/profile/security
    // ------------------------------------------------------------------

    public function testSecurityPageRequiresAuthentication(): void
    {
        $this->client->request('GET', '/en/profile/security');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testSecurityPageIsAccessibleWhenLoggedIn(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'main');

        $this->client->request('GET', '/en/profile/security');

        $this->assertResponseIsSuccessful();
    }

    // ------------------------------------------------------------------
    // POST /en/profile/security/enable
    // ------------------------------------------------------------------

    public function testEnableGeneratesSecretAndRedirects(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'main');

        $this->client->request('POST', '/en/profile/security/enable', [
            '_token' => $this->getCsrfToken('totp_enable'),
        ]);

        $this->assertResponseRedirects('/en/profile/security');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->refresh($user);

        $this->assertNotNull($user->getTotpSecret());
        $this->assertFalse($user->isTotpEnabled());
    }

    public function testEnableWithInvalidCsrfThrowsAccessDenied(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'main');

        $this->client->request('POST', '/en/profile/security/enable', [
            '_token' => 'invalid-token',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    // ------------------------------------------------------------------
    // POST /en/profile/security/confirm
    // ------------------------------------------------------------------

    public function testConfirmWithInvalidCodeKeepsTotpDisabled(): void
    {
        $user = $this->setupUserWithPendingTotp();
        $this->client->loginUser($user, 'main');

        $this->client->request('POST', '/en/profile/security/confirm', [
            '_token' => $this->getCsrfToken('totp_confirm'),
            'totp_code' => '000000',
        ]);

        $this->assertResponseRedirects('/en/profile/security');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->refresh($user);

        $this->assertFalse($user->isTotpEnabled());
    }

    // ------------------------------------------------------------------
    // POST /en/profile/security/disable
    // ------------------------------------------------------------------

    public function testDisableWithInvalidPasswordKeepsTotpEnabled(): void
    {
        $user = $this->setupUserWithTotpEnabled();
        $this->client->loginUser($user, 'main');

        $this->client->request('POST', '/en/profile/security/disable', [
            '_token' => $this->getCsrfToken('totp_disable'),
            'current_password' => 'wrongpassword',
        ]);

        $this->assertResponseRedirects('/en/profile/security');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->refresh($user);

        $this->assertTrue($user->isTotpEnabled());
        $this->assertNotNull($user->getTotpSecret());
    }

    public function testDisableWithValidPasswordClearsTotp(): void
    {
        $user = $this->setupUserWithTotpEnabled();
        $this->client->loginUser($user, 'main');

        $this->client->request('POST', '/en/profile/security/disable', [
            '_token' => $this->getCsrfToken('totp_disable'),
            'current_password' => 'password',
        ]);

        $this->assertResponseRedirects('/en/profile/security');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->refresh($user);

        $this->assertFalse($user->isTotpEnabled());
        $this->assertNull($user->getTotpSecret());
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function getCsrfToken(string $intention): string
    {
        /** @var CsrfTokenManagerInterface $manager */
        $manager = static::getContainer()->get('security.csrf.token_manager');
        return $manager->getToken($intention)->getValue();
    }

    private function setupUserWithPendingTotp(): User
    {
        $proxy = UserFactory::new()->withCredentials(
            'totp-pending@test.com',
            'totppending',
            'password'
        )->create();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        /** @var User $user */
        $user = $em->find(User::class, $proxy->getId());

        $user->setTotpSecret('BASE32SECRETFORTEST');
        $user->setTotpEnabled(false);
        $em->flush();

        return $user;
    }

    private function setupUserWithTotpEnabled(): User
    {
        $proxy = UserFactory::new()->withCredentials(
            'totp-enabled@test.com',
            'totpenabled',
            'password'
        )->create();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        /** @var User $user */
        $user = $em->find(User::class, $proxy->getId());

        $user->setTotpSecret('BASE32SECRETFORTEST');
        $user->setTotpEnabled(true);
        $em->flush();

        return $user;
    }
}
