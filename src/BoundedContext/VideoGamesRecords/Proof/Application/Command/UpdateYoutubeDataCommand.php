<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\Command;

use App\BoundedContext\VideoGamesRecords\Proof\Application\Handler\YoutubeDataHandler;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\VideoRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vgr:youtube:update-data',
    description: 'Update YouTube metadata (title, thumbnail, views, likes) for the 100 latest videos'
)]
class UpdateYoutubeDataCommand extends Command
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly YoutubeDataHandler $youtubeDataHandler
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $videos = $this->videoRepository->findLatestYoutubeVideos(100);

        foreach ($videos as $video) {
            $this->youtubeDataHandler->process($video);
        }

        $io->success(sprintf('%d YouTube video(s) updated.', count($videos)));

        return Command::SUCCESS;
    }
}
