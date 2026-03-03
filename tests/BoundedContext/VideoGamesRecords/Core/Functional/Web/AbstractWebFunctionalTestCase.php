<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\BoundedContext\User\Domain\Entity\User;
use App\Tests\BoundedContext\User\Story\AdminUserStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

abstract class AbstractWebFunctionalTestCase extends WebTestCase
{
    use Factories;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    /**
     * Returns the fixture player user (user@local.fr).
     * This user has ROLE_PLAYER via the Player group (id=2) assigned by CreatePlayerListener.
     * Returns the actual Doctrine entity for use with $client->loginUser().
     */
    protected function getPlayerUser(): User
    {
        $proxy = AdminUserStory::regularUser();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine')->getManager();

        /** @var User $user */
        $user = $em->find(User::class, $proxy->getId());

        return $user;
    }
}
