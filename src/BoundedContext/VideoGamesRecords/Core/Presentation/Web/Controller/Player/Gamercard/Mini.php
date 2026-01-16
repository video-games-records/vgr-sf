<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Gamercard;

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
use App\SharedKernel\Domain\Traits\GetOrdinalSuffixTrait;
use App\SharedKernel\Domain\Traits\NumberFormatTrait;

class Mini extends AbstractController
{
    use GetOrdinalSuffixTrait;
    use NumberFormatTrait;

    private FilesystemOperator $appStorage;

    public function __construct(FilesystemOperator $appStorage)
    {
        $this->appStorage = $appStorage;
    }

    #[Route(
        '/gamercard/{id}/mini',
        name: 'vgr_core_player_gamercard_mini',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    #[Cache(maxage: 3600, public: true, mustRevalidate: true)]
    public function __invoke(Player $player): Response
    {
        $manager = ImageManager::gd();

        // Load base gamercard image
        $projectDir = $this->getParameter('kernel.project_dir');
        assert(is_string($projectDir));
        $baseImagePath = $projectDir . '/src/SharedKernel/Resources/img/gamercard/mini.png';
        $gamercard = $manager->read($baseImagePath);

        // Fonts paths
        $fontPath = $projectDir . '/src/SharedKernel/Resources/fonts/';
        $segoeUISemiBold = $fontPath . 'seguisb.ttf';

        // Ranking Points
        $fontSize = 10;
        $textY = 17;
        $pointsText = $this->numberFormat($player->getPointGame()) . ' Pts';

        $gamercard->text($pointsText, 40, $textY, function (FontFactory $font) use ($segoeUISemiBold, $fontSize) {
            $font->filename($segoeUISemiBold);
            $font->size($fontSize);
            $font->color('rgb(255, 218, 176)');
            $font->valign('middle');
            $font->align('left');
        });

        $gamercard->text('/', 124, $textY, function (FontFactory $font) use ($segoeUISemiBold, $fontSize) {
            $font->filename($segoeUISemiBold);
            $font->size($fontSize);
            $font->color('rgb(255, 218, 176)');
            $font->valign('middle');
            $font->align('left');
        });

        $rankText = $player->getRankPointGame() . ' ' . $this->getOrdinalSuffix($player->getRankPointGame());
        $gamercard->text($rankText, 130, $textY, function (FontFactory $font) use ($segoeUISemiBold, $fontSize) {
            $font->filename($segoeUISemiBold);
            $font->size($fontSize);
            $font->color('rgb(255, 191, 1)');
            $font->valign('middle');
            $font->align('left');
        });

        // Ranking Medals - Add sprite icons
        $spritePath = $projectDir . '/src/SharedKernel/Resources/img/sprite.png';
        $sprite = $manager->read($spritePath);

        $medals = [
            [126, 160, 164, 8],
            [108, 160, 211, 8],
            [92, 160, 258, 8],
            [74, 160, 305, 8],
        ];

        foreach ($medals as [$srcX, $srcY, $dstX, $dstY]) {
            $medalIcon = clone $sprite;
            $medalIcon->crop(16, 16, $srcX, $srcY);
            $gamercard->place($medalIcon, 'top-left', $dstX, $dstY);
        }

        // Medal counts
        $medalTexts = [
            [(string) $player->getChartRank0(), 180],
            [(string) $player->getChartRank1(), 227],
            [(string) $player->getChartRank2(), 274],
            [(string) $player->getChartRank3(), 321],
        ];

        foreach ($medalTexts as [$text, $x]) {
            $gamercard->text($text, $x, $textY, function (FontFactory $font) use ($segoeUISemiBold, $fontSize) {
                $font->filename($segoeUISemiBold);
                $font->size($fontSize);
                $font->color('rgb(255, 218, 176)');
                $font->valign('middle');
                $font->align('left');
            });
        }

        $gamercard->text('/', 350, $textY, function (FontFactory $font) use ($segoeUISemiBold, $fontSize) {
            $font->filename($segoeUISemiBold);
            $font->size($fontSize);
            $font->color('rgb(255, 218, 176)');
            $font->valign('middle');
            $font->align('left');
        });

        $rank = $player->getRankMedal();
        if ($rank <= 99) {
            $rank .= $this->getOrdinalSuffix($rank);
        }

        $gamercard->text((string) $rank, 356, $textY, function (FontFactory $font) use ($segoeUISemiBold, $fontSize) {
            $font->filename($segoeUISemiBold);
            $font->size($fontSize);
            $font->color('rgb(255, 191, 1)');
            $font->valign('middle');
            $font->align('left');
        });

        // Add avatar
        $avatarData = $this->getAvatar($player);
        $avatar = $manager->read($avatarData);
        $avatar->resize(26, 26);
        $gamercard->place($avatar, 'top-left', 4, 2);

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
