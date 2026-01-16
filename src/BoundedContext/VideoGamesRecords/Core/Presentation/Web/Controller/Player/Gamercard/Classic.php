<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Gamercard;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\SharedKernel\Domain\Traits\GetOrdinalSuffixTrait;
use App\SharedKernel\Domain\Traits\NumberFormatTrait;

class Classic extends AbstractController
{
    use GetOrdinalSuffixTrait;
    use NumberFormatTrait;

    private FilesystemOperator $appStorage;
    private PlayerGameRepository $playerGameRepository;

    public function __construct(
        FilesystemOperator $appStorage,
        PlayerGameRepository $playerGameRepository
    ) {
        $this->appStorage = $appStorage;
        $this->playerGameRepository = $playerGameRepository;
    }

    /**
     * @throws FilesystemException
     */
    #[Route(
        '/gamercard/{id}/classic',
        name: 'vgr_core_player_gamercard_classic',
        methods: ['GET'],
        requirements: ['id' => '[1-9]\d*']
    )]
    #[Cache(maxage: 3600, public: true, mustRevalidate: true)]
    public function __invoke(Player $player): Response
    {
        $manager = ImageManager::gd();
        $gamercard = $manager->create(210, 135);

        // Background rectangles
        $gamercard->drawRectangle(0, 0, function ($rectangle) {
            $rectangle->size(210, 24);
            $rectangle->background('rgb(13, 14, 15)');
        });

        $gamercard->drawRectangle(0, 25, function ($rectangle) {
            $rectangle->size(210, 110);
            $rectangle->background('rgb(58, 56, 56)');
        });

        // Lines
        $gamercard->drawRectangle(78, 52, function ($rectangle) {
            $rectangle->size(126, 1);
            $rectangle->background('rgb(196, 196, 196)');
        });

        $gamercard->drawRectangle(78, 52, function ($rectangle) {
            $rectangle->size(125, 1);
            $rectangle->background('rgb(86, 86, 86)');
        });

        // Fonts paths
        $projectDir = $this->getParameter('kernel.project_dir');
        assert(is_string($projectDir));
        $fontPath = $projectDir . '/src/SharedKernel/Resources/fonts/';
        $segoeUILight = $fontPath . 'segoeuil.ttf';
        $segoeUISemiBold = $fontPath . 'seguisb.ttf';

        // Pseudo
        if ($player->getTeam() !== null) {
            $pseudo = sprintf('[%s] %s', $player->getTeam()->getTag(), $player->getPseudo());
        } else {
            $pseudo = $player->getPseudo();
        }

        $gamercard->text($pseudo, 9, 8, function (FontFactory $font) use ($segoeUILight) {
            $font->filename($segoeUILight);
            $font->size(12.375);
            $font->color('rgb(246, 162, 83)');
            $font->valign('top');
            $font->align('left');
        });

        // Ranking
        $fontSize = 9;
        $rankMedal = '/' . $player->getRankMedal();
        if ($player->getRankMedal() <= 999) {
            $rankMedal .= $this->getOrdinalSuffix($player->getRankMedal());
        }
        $pointGame = $this->numberFormat($player->getPointGame()) . ' Pts / ';
        $pointGame .= $player->getRankPointGame() . $this->getOrdinalSuffix($player->getRankPointGame());

        $textData = [
            [(string) $player->getChartRank0(), 96, 63],
            [(string) $player->getChartRank1(), 145, 63],
            [(string) $player->getChartRank2(), 96, 83],
            [(string) $player->getChartRank3(), 145, 83],
            [$rankMedal, 175, 73],
            [$pointGame, 82, 40],
        ];

        foreach ($textData as [$text, $x, $y]) {
            $gamercard->text($text, $x, $y, function (FontFactory $font) use ($segoeUISemiBold, $fontSize) {
                $font->filename($segoeUISemiBold);
                $font->size($fontSize);
                $font->color('rgb(255, 255, 255)');
                $font->valign('top');
                $font->align('left');
            });
        }

        // Add sprites pictures medals
        $spritePath = $projectDir . '/src/SharedKernel/Resources/img/sprite.png';
        $sprite = $manager->read($spritePath);

        $medals = [
            [126, 160, 78, 59],
            [108, 160, 127, 59],
            [92, 160, 78, 79],
            [74, 160, 127, 79],
        ];

        foreach ($medals as [$srcX, $srcY, $dstX, $dstY]) {
            $medalIcon = clone $sprite;
            $medalIcon->crop(16, 16, $srcX, $srcY);
            $gamercard->place($medalIcon, 'top-left', $dstX, $dstY);
        }

        // Add avatar
        $avatarData = $this->getAvatar($player);
        $avatar = $manager->read($avatarData);
        $avatar->resize(64, 64);
        $gamercard->place($avatar, 'top-left', 9, 30);

        /** @var array<PlayerGame> $playerGames */
        $playerGames = $this->playerGameRepository->findBy(['player' => $player], ['lastUpdate' => 'DESC'], 5);

        $startX = 9;
        foreach ($playerGames as $playerGame) {
            $badge = $playerGame->getGame()->getBadge();
            $badgeData = $this->getBadge($badge);
            $badgeImage = $manager->read($badgeData);
            $gamercard->place($badgeImage, 'top-left', $startX, 99);
            $startX += 38;
        }

        return new Response(
            (string) $gamercard->toPng(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'image/png',
            ]
        );
    }


    /**
     * @param Player $player
     * @return string
     * @throws FilesystemException
     */
    public function getAvatar(Player $player): string
    {
        $path = 'user' . DIRECTORY_SEPARATOR . $player->getAvatar();
        if (!$this->appStorage->fileExists($path)) {
            $path = 'user' . DIRECTORY_SEPARATOR . 'default.png';
        }
        return $this->appStorage->read($path);
    }


    /**
     * @param Badge $badge
     * @return string
     * @throws FilesystemException
     */
    public function getBadge(Badge $badge): string
    {
        $path = $badge->getType()->getDirectory() . DIRECTORY_SEPARATOR . $badge->getPicture();
        if (!$this->appStorage->fileExists($path)) {
            $path = 'badge' . DIRECTORY_SEPARATOR . 'default.gif';
        }
        return $this->appStorage->read($path);
    }
}
