<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Functional\Web;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamRequest;
use App\Tests\BoundedContext\User\Factory\UserFactory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web\AbstractWebFunctionalTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class TeamOpenCloseWorkflowTest extends AbstractWebFunctionalTestCase
{
    public function testLeaderCanOpenTeamAndAnotherPlayerCanThenSendJoinRequest(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        // Leader + team, initially CLOSED (entity default).
        // CreatePlayerListener auto-creates a matching Player (same id as the User) on User persist.
        $leaderUser = UserFactory::new()
            ->asUser()
            ->withCredentials('leader-workflow@local.fr', 'leader-workflow')
            ->create();

        $leaderPlayer = $em->getRepository(Player::class)->findOneBy(['user_id' => $leaderUser->getId()]);
        self::assertNotNull($leaderPlayer, 'CreatePlayerListener should have auto-created a Player for the leader user');

        $team = new Team();
        $team->setLibTeam('Workflow Test Team');
        $team->setTag('WFT');
        $team->setLeader($leaderPlayer);
        $em->persist($team);
        $em->flush();

        $leaderPlayer->setTeam($team);
        $em->flush();

        self::assertSame(Team::STATUS_CLOSED, $team->getStatus(), 'Team should start closed by default');

        $teamId = $team->getId();
        $teamSlug = $team->getSlug();
        $leaderUserId = $leaderUser->getId();

        // Joiner: existing fixture player with no team (user@local.fr)
        $joinerUser = $this->getPlayerUser();
        $joinerUserId = $joinerUser->getId();

        // --- Step 1: team closed -> no join button on public profile ---
        // The KernelBrowser reboots the kernel on each request, so entities from the
        // previous EntityManager instance become stale: re-fetch a fresh EM/entities each time.
        $this->client->loginUser($joinerUser, 'user');
        $crawler = $this->client->request('GET', sprintf('/en/team/%d-%s', $teamId, $teamSlug));
        $this->assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('form[action$="/join"]'), 'Join form must not be present while team is closed');

        // --- Step 2: leader opens the team via /team/manage ---
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $leaderUser = $em->getRepository(User::class)->find($leaderUserId);
        self::assertNotNull($leaderUser);
        $this->client->loginUser($leaderUser, 'user');
        $crawler = $this->client->request('GET', '/en/team/manage');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->first()->form();
        $statusField = $form->get('edit_team_form[status]');
        self::assertInstanceOf(ChoiceFormField::class, $statusField);
        $statusField->select(Team::STATUS_OPENED);
        $this->client->submit($form);

        $this->assertResponseRedirects('/en/team/manage');

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $team = $em->getRepository(Team::class)->find($teamId);
        self::assertNotNull($team);
        self::assertSame(Team::STATUS_OPENED, $team->getStatus(), 'Team status should be OPENED after leader submits the form');

        // --- Step 3: team open -> join button appears, and join request can be sent ---
        $joinerUser = $em->getRepository(User::class)->find($joinerUserId);
        self::assertNotNull($joinerUser);
        $this->client->loginUser($joinerUser, 'user');
        $crawler = $this->client->request('GET', sprintf('/en/team/%d-%s', $teamId, $teamSlug));
        $this->assertResponseIsSuccessful();

        $joinForms = $crawler->filter('form[action$="/join"]');
        self::assertCount(1, $joinForms, 'Join form must be present once the team is open');

        $joinForm = $joinForms->form();
        $this->client->submit($joinForm);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $joinerPlayer = $em->getRepository(Player::class)->findOneBy(['user_id' => $joinerUserId]);
        self::assertNotNull($joinerPlayer);

        $teamRequest = $em->getRepository(TeamRequest::class)->findOneBy([
            'team' => $teamId,
            'player' => $joinerPlayer,
        ]);

        self::assertNotNull($teamRequest, 'A TeamRequest must have been created for the joining player');
        self::assertSame('ACTIVE', $teamRequest->getStatus()->value);
    }
}
