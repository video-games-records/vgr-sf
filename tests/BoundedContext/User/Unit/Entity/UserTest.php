<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Entity;

use App\BoundedContext\User\Domain\Entity\Group;
use App\BoundedContext\User\Domain\Entity\User;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyGroupsCollection(): void
    {
        $user = new User();

        $this->assertInstanceOf(Collection::class, $user->getGroups());
        $this->assertCount(0, $user->getGroups());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->user->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->user->setId(99);

        $this->assertSame(99, $this->user->getId());
        $this->assertSame($this->user, $result);
    }

    public function testSetIdToNull(): void
    {
        $this->user->setId(1);
        $this->user->setId(null);

        $this->assertNull($this->user->getId());
    }

    public function testSetAndGetUsername(): void
    {
        $result = $this->user->setUsername('johndoe');

        $this->assertSame('johndoe', $this->user->getUsername());
        $this->assertSame($this->user, $result);
    }

    public function testSetAndGetEmail(): void
    {
        $result = $this->user->setEmail('john@example.com');

        $this->assertSame('john@example.com', $this->user->getEmail());
        $this->assertSame($this->user, $result);
    }

    public function testEnabledDefaultsToTrue(): void
    {
        $this->assertTrue($this->user->isEnabled());
    }

    public function testSetAndGetEnabled(): void
    {
        $result = $this->user->setEnabled(false);

        $this->assertFalse($this->user->isEnabled());
        $this->assertSame($this->user, $result);
    }

    public function testAvatarDefaultsToDefaultPng(): void
    {
        $this->assertSame('default.png', $this->user->getAvatar());
    }

    public function testSetAndGetAvatar(): void
    {
        $result = $this->user->setAvatar('custom.jpg');

        $this->assertSame('custom.jpg', $this->user->getAvatar());
        $this->assertSame($this->user, $result);
    }

    public function testLanguageDefaultsToEn(): void
    {
        $this->assertSame('en', $this->user->getLanguage());
    }

    public function testSetAndGetLanguage(): void
    {
        $result = $this->user->setLanguage('fr');

        $this->assertSame('fr', $this->user->getLanguage());
        $this->assertSame($this->user, $result);
    }

    public function testNbConnexionDefaultsToZero(): void
    {
        $this->assertSame(0, $this->user->getNbConnexion());
    }

    public function testSetAndGetNbConnexion(): void
    {
        $result = $this->user->setNbConnexion(42);

        $this->assertSame(42, $this->user->getNbConnexion());
        $this->assertSame($this->user, $result);
    }

    public function testNbForumMessageDefaultsToZero(): void
    {
        $this->assertSame(0, $this->user->getNbForumMessage());
    }

    public function testSetAndGetNbForumMessage(): void
    {
        $result = $this->user->setNbForumMessage(10);

        $this->assertSame(10, $this->user->getNbForumMessage());
        $this->assertSame($this->user, $result);
    }

    public function testCommentDefaultsToNull(): void
    {
        $this->assertNull($this->user->getComment());
    }

    public function testSetAndGetComment(): void
    {
        $result = $this->user->setComment('A user comment');

        $this->assertSame('A user comment', $this->user->getComment());
        $this->assertSame($this->user, $result);
    }

    public function testSetCommentToNull(): void
    {
        $this->user->setComment('Something');
        $result = $this->user->setComment(null);

        $this->assertNull($this->user->getComment());
        $this->assertSame($this->user, $result);
    }

    // ------------------------------------------------------------------
    // Password
    // ------------------------------------------------------------------

    public function testSetAndGetPassword(): void
    {
        $result = $this->user->setPassword('hashedpassword');

        $this->assertSame('hashedpassword', $this->user->getPassword());
        $this->assertSame($this->user, $result);
    }

    public function testGetSaltReturnsNull(): void
    {
        $this->assertNull($this->user->getSalt());
    }

    public function testSetAndGetPlainPassword(): void
    {
        $result = $this->user->setPlainPassword('plaintext');

        $this->assertSame('plaintext', $this->user->getPlainPassword());
        $this->assertSame($this->user, $result);
    }

    public function testSetPlainPasswordToNull(): void
    {
        $this->user->setPlainPassword('secret');
        $this->user->setPlainPassword(null);

        $this->assertNull($this->user->getPlainPassword());
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->user->eraseCredentials();
    }

    // ------------------------------------------------------------------
    // ConfirmationToken
    // ------------------------------------------------------------------

    public function testConfirmationTokenDefaultsToNull(): void
    {
        $this->assertNull($this->user->getConfirmationToken());
    }

    public function testSetAndGetConfirmationToken(): void
    {
        $result = $this->user->setConfirmationToken('abc123token');

        $this->assertSame('abc123token', $this->user->getConfirmationToken());
        $this->assertSame($this->user, $result);
    }

    public function testSetConfirmationTokenToNull(): void
    {
        $this->user->setConfirmationToken('sometoken');
        $result = $this->user->setConfirmationToken(null);

        $this->assertNull($this->user->getConfirmationToken());
        $this->assertSame($this->user, $result);
    }

    // ------------------------------------------------------------------
    // LastLogin & connexion counter
    // ------------------------------------------------------------------

    public function testLastLoginDefaultsToNull(): void
    {
        $this->assertNull($this->user->getLastLogin());
    }

    public function testSetLastLoginUpdatesLastLogin(): void
    {
        $date = new DateTime('2025-03-15 08:00:00');
        $result = $this->user->setLastLogin($date);

        $this->assertSame($date, $this->user->getLastLogin());
        $this->assertSame($this->user, $result);
    }

    public function testSetLastLoginIncrementsNbConnexionOnNewDay(): void
    {
        $day1 = new DateTime('2025-01-01 10:00:00');
        $this->user->setLastLogin($day1);

        $initialCount = $this->user->getNbConnexion();

        $day2 = new DateTime('2025-01-02 10:00:00');
        $this->user->setLastLogin($day2);

        $this->assertSame($initialCount + 1, $this->user->getNbConnexion());
    }

    public function testSetLastLoginDoesNotIncrementNbConnexionOnSameDay(): void
    {
        $firstLogin = new DateTime('2025-01-01 08:00:00');
        $this->user->setLastLogin($firstLogin);

        $countAfterFirst = $this->user->getNbConnexion();

        $secondLogin = new DateTime('2025-01-01 20:00:00');
        $this->user->setLastLogin($secondLogin);

        $this->assertSame($countAfterFirst, $this->user->getNbConnexion());
    }

    public function testUpdateLastLoginOnlyDoesNotIncrementNbConnexion(): void
    {
        $this->user->setNbConnexion(5);
        $date = new DateTime('2025-06-01');
        $this->user->updateLastLoginOnly($date);

        $this->assertSame($date, $this->user->getLastLogin());
        $this->assertSame(5, $this->user->getNbConnexion());
    }

    // ------------------------------------------------------------------
    // PasswordRequestedAt
    // ------------------------------------------------------------------

    public function testPasswordRequestedAtDefaultsToNull(): void
    {
        $this->assertNull($this->user->getPasswordRequestedAt());
    }

    public function testSetAndGetPasswordRequestedAt(): void
    {
        $date = new DateTime('2025-05-10');
        $result = $this->user->setPasswordRequestedAt($date);

        $this->assertSame($date, $this->user->getPasswordRequestedAt());
        $this->assertSame($this->user, $result);
    }

    public function testSetPasswordRequestedAtToNull(): void
    {
        $this->user->setPasswordRequestedAt(new DateTime());
        $result = $this->user->setPasswordRequestedAt(null);

        $this->assertNull($this->user->getPasswordRequestedAt());
        $this->assertSame($this->user, $result);
    }

    // ------------------------------------------------------------------
    // isPasswordRequestExpired
    // ------------------------------------------------------------------

    public function testIsPasswordRequestExpiredReturnsTrueWhenExpired(): void
    {
        $past = new DateTime('-2 hours');
        $this->user->setPasswordRequestedAt($past);

        $this->assertTrue($this->user->isPasswordRequestExpired(3600));
    }

    public function testIsPasswordRequestExpiredReturnsFalseWhenNotExpired(): void
    {
        $recent = new DateTime('-30 minutes');
        $this->user->setPasswordRequestedAt($recent);

        $this->assertFalse($this->user->isPasswordRequestExpired(3600));
    }

    public function testIsPasswordRequestExpiredReturnsFalseWhenPasswordRequestedAtIsNull(): void
    {
        $this->assertFalse($this->user->isPasswordRequestExpired(3600));
    }

    // ------------------------------------------------------------------
    // Roles management
    // ------------------------------------------------------------------

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $roles = $this->user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
    }

    public function testSetAndGetRoles(): void
    {
        $result = $this->user->setRoles(['ROLE_ADMIN']);

        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertSame($this->user, $result);
    }

    public function testAddRole(): void
    {
        $this->user->addRole('ROLE_EDITOR');

        $this->assertContains('ROLE_EDITOR', $this->user->getRoles());
    }

    public function testAddRoleIsCaseNormalized(): void
    {
        $this->user->addRole('role_moderator');

        $this->assertContains('ROLE_MODERATOR', $this->user->getRoles());
    }

    public function testAddRoleDoesNotDuplicate(): void
    {
        $this->user->addRole('ROLE_ADMIN');
        $this->user->addRole('ROLE_ADMIN');

        $roles = $this->user->getRoles();
        $this->assertSame(1, count(array_filter($roles, fn ($r) => $r === 'ROLE_ADMIN')));
    }

    public function testRemoveRole(): void
    {
        $this->user->addRole('ROLE_EDITOR');
        $this->user->removeRole('ROLE_EDITOR');

        $this->assertNotContains('ROLE_EDITOR', $this->user->getRoles());
    }

    public function testRemoveRoleIsCaseInsensitive(): void
    {
        $this->user->addRole('ROLE_EDITOR');
        $this->user->removeRole('role_editor');

        $this->assertNotContains('ROLE_EDITOR', $this->user->getRoles());
    }

    public function testRemoveNonExistentRoleDoesNothing(): void
    {
        $countBefore = count($this->user->getRoles());
        $this->user->removeRole('ROLE_NONEXISTENT');

        $this->assertCount($countBefore, $this->user->getRoles());
    }

    public function testHasRoleReturnsTrueWhenRoleExists(): void
    {
        $this->user->addRole('ROLE_ADMIN');

        $this->assertTrue($this->user->hasRole('ROLE_ADMIN'));
    }

    public function testHasRoleIsCaseInsensitive(): void
    {
        $this->user->addRole('ROLE_ADMIN');

        $this->assertTrue($this->user->hasRole('role_admin'));
    }

    public function testHasRoleReturnsFalseWhenRoleAbsent(): void
    {
        $this->assertFalse($this->user->hasRole('ROLE_SUPERADMIN'));
    }

    // ------------------------------------------------------------------
    // Groups collection
    // ------------------------------------------------------------------

    public function testAddGroup(): void
    {
        $group = $this->createMock(Group::class);
        $this->user->addGroup($group);

        $this->assertCount(1, $this->user->getGroups());
        $this->assertTrue($this->user->getGroups()->contains($group));
    }

    public function testAddGroupDoesNotDuplicate(): void
    {
        $group = $this->createMock(Group::class);
        $this->user->addGroup($group);
        $this->user->addGroup($group);

        $this->assertCount(1, $this->user->getGroups());
    }

    public function testRemoveGroup(): void
    {
        $group = $this->createMock(Group::class);
        $this->user->addGroup($group);
        $this->user->removeGroup($group);

        $this->assertCount(0, $this->user->getGroups());
    }

    public function testGetRolesMergesGroupRoles(): void
    {
        $group = $this->createMock(Group::class);
        $group->method('getRoles')->willReturn(['ROLE_EDITOR', 'ROLE_USER']);

        $this->user->addGroup($group);

        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_EDITOR', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    // ------------------------------------------------------------------
    // getUserIdentifier
    // ------------------------------------------------------------------

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $this->user->setEmail('alice@example.com');

        $this->assertSame('alice@example.com', $this->user->getUserIdentifier());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->user->setId(7);
        $this->user->setUsername('bobsmith');

        $this->assertSame('bobsmith [7]', (string) $this->user);
    }
}
