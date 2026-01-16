<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\MessageHandler\Player;

use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerCountryRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

#[AsMessageHandler]
readonly class UpdatePlayerCountryRankHandler
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function __invoke(UpdatePlayerCountryRank $updatePlayerCountryRank): void
    {
        /** @var Country|null $country */
        $country = $this->em->getRepository(Country::class)
            ->find($updatePlayerCountryRank->getCountryId());

        if (null === $country) {
            throw new EntityNotFoundException('Country', $updatePlayerCountryRank->getCountryId());
        }

        $players = $this->em->getRepository(Player::class)
            ->findBy(['country' => $country], ['rankPointChart' => 'ASC']);
        RankingTools::addObjectRank($players, 'rankCountry', ['rankPointGame']);
        $this->em->flush();

        // Update badges directly (was in PlayerCountryUpdatedSubscriber - now optimized)
        if ($country->getBadge()) {
            // Get first place players from the ranking we just calculated
            $firstPlacePlayers = [];
            foreach ($players as $player) {
                if ($player->getRankCountry() === 1) {
                    $firstPlacePlayers[$player->getId()] = 0;
                } else {
                    break; // Rankings are ordered, so no more first places
                }
            }

            $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge')
                ->updateBadge($firstPlacePlayers, $country->getBadge());
        }
    }
}
