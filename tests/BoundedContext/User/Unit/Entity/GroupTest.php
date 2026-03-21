<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Entity;

use App\BoundedContext\User\Domain\Entity\Group;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    private Group $group;

    protected function setUp(): void
    {
        $this->group = new Group('TestGroup');
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorSetsName(): void
    {
        $group = new Group('Admins');

        $this->assertSame('Admins', $group->getName());
    }

    public function testConstructorSetsRoles(): void
    {
        $group = new Group('Editors', ['ROLE_EDITOR', 'ROLE_USER']);

        $this->assertSame(['ROLE_EDITOR', 'ROLE_USER'], $group->getRoles());
    }

    public function testConstructorDefaultsRolesToEmpty(): void
    {
        $group = new Group('NoRoles');

        $this->assertSame([], $group->getRoles());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->group->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->group->setId(42);

        $this->assertSame(42, $this->group->getId());
        $this->assertSame($this->group, $result);
    }

    public function testSetIdToNull(): void
    {
        $this->group->setId(1);
        $this->group->setId(null);

        $this->assertNull($this->group->getId());
    }

    public function testSetAndGetName(): void
    {
        $result = $this->group->setName('Moderators');

        $this->assertSame('Moderators', $this->group->getName());
        $this->assertSame($this->group, $result);
    }

    // ------------------------------------------------------------------
    // Roles management
    // ------------------------------------------------------------------

    public function testSetAndGetRoles(): void
    {
        $result = $this->group->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->assertSame(['ROLE_ADMIN', 'ROLE_USER'], $this->group->getRoles());
        $this->assertSame($this->group, $result);
    }

    public function testSetRolesEmptyArray(): void
    {
        $this->group->setRoles(['ROLE_ADMIN']);
        $result = $this->group->setRoles([]);

        $this->assertSame([], $this->group->getRoles());
        $this->assertSame($this->group, $result);
    }

    // ------------------------------------------------------------------
    // hasRole
    // ------------------------------------------------------------------

    public function testHasRoleReturnsTrueWhenRoleExists(): void
    {
        $this->group->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->assertTrue($this->group->hasRole('ROLE_ADMIN'));
    }

    public function testHasRoleIsCaseInsensitive(): void
    {
        $this->group->setRoles(['ROLE_ADMIN']);

        $this->assertTrue($this->group->hasRole('role_admin'));
    }

    public function testHasRoleReturnsFalseWhenRoleDoesNotExist(): void
    {
        $this->group->setRoles(['ROLE_USER']);

        $this->assertFalse($this->group->hasRole('ROLE_ADMIN'));
    }

    public function testHasRoleReturnsFalseOnEmptyRoles(): void
    {
        $this->group->setRoles([]);

        $this->assertFalse($this->group->hasRole('ROLE_USER'));
    }

    // ------------------------------------------------------------------
    // removeRole
    // ------------------------------------------------------------------

    public function testRemoveRoleRemovesExistingRole(): void
    {
        $this->group->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $result = $this->group->removeRole('ROLE_ADMIN');

        $this->assertSame(['ROLE_USER'], $this->group->getRoles());
        $this->assertSame($this->group, $result);
    }

    public function testRemoveRoleIsCaseInsensitive(): void
    {
        $this->group->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $this->group->removeRole('role_admin');

        $this->assertSame(['ROLE_USER'], $this->group->getRoles());
    }

    public function testRemoveNonExistentRoleDoesNothing(): void
    {
        $this->group->setRoles(['ROLE_USER']);
        $result = $this->group->removeRole('ROLE_ADMIN');

        $this->assertSame(['ROLE_USER'], $this->group->getRoles());
        $this->assertSame($this->group, $result);
    }

    public function testRemoveRoleReindexesArray(): void
    {
        $this->group->setRoles(['ROLE_ADMIN', 'ROLE_EDITOR', 'ROLE_USER']);
        $this->group->removeRole('ROLE_EDITOR');

        $roles = $this->group->getRoles();
        $this->assertSame(['ROLE_ADMIN', 'ROLE_USER'], array_values($roles));
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->group->setId(5);
        $this->group->setName('SuperAdmins');

        $this->assertSame('SuperAdmins [5]', (string) $this->group);
    }
}
