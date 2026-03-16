<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\Command;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vgr:team:create-forums',
    description: 'Associate existing forums to teams by matching name'
)]
class CreateTeamForumsCommand extends Command
{
    public const int CATEGORY_ID = 9;

    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('create-if-not-exists', null, InputOption::VALUE_NONE, 'Create a forum if no matching forum is found');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $createIfNotExists = $input->getOption('create-if-not-exists');

        $forumRepository = $this->em->getRepository('App\BoundedContext\Forum\Domain\Entity\Forum');
        $category = $this->em->getRepository('App\BoundedContext\Forum\Domain\Entity\Category')
            ->findOneBy(['id' => self::CATEGORY_ID]);

        $teams = $this->teamRepository->findAll();
        $linked = 0;
        $created = 0;
        $notFound = 0;

        foreach ($teams as $team) {
            if ($team->getForum() !== null) {
                continue;
            }

            $forum = $forumRepository->findOneBy([
                'libForum' => $team->getLibTeam(),
                'category' => self::CATEGORY_ID,
            ]);

            if ($forum === null) {
                if ($createIfNotExists) {
                    $forum = new Forum();
                    $forum->setLibForum($team->getLibTeam());
                    $forum->setLibForumFr($team->getLibTeam());
                    $forum->setCategory($category);
                    $this->em->persist($forum);
                    $team->setForum($forum);
                    $io->text(sprintf('[%d] %s → forum created', $team->getId(), $team->getLibTeam()));
                    $created++;
                } else {
                    $io->text(sprintf('[%d] %s → no matching forum found', $team->getId(), $team->getLibTeam()));
                    $notFound++;
                }
                continue;
            }

            $team->setForum($forum);
            $io->text(sprintf('[%d] %s → linked to forum #%d', $team->getId(), $team->getLibTeam(), $forum->getId()));
            $linked++;
        }

        $this->em->flush();

        $io->success(sprintf('%d linked, %d created, %d not found.', $linked, $created, $notFound));

        return Command::SUCCESS;
    }
}
