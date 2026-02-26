<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Scheduler;

use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\DailyRanking;
use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdateGame;
use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdatePlayer;
use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdateTeam;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/*use VideoGamesRecords\CoreBundle\Scheduler\Message\DesactivateScore;
use VideoGamesRecords\CoreBundle\Scheduler\Message\PurgeLostPosition;
use VideoGamesRecords\CoreBundle\Scheduler\Message\UpdatePlayerBadge;
use VideoGamesRecords\CoreBundle\Scheduler\Message\UpdateYoutubeData;*/

#[AsSchedule('default')]
class Scheduler implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;

    public function __construct()
    {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();

        // APP
        //$schedule->add(RecurringMessage::cron('0 22 * * *', new UpdateUserRole()));

        // DWH Updates - Using new DDD messages
        $schedule
            ->add(RecurringMessage::cron('5 0 * * *', new RedispatchMessage(new UpdateGame(), 'async')))
            ->add(RecurringMessage::cron('5 0 * * *', new RedispatchMessage(new UpdatePlayer(), 'async')))
            ->add(RecurringMessage::cron('5 0 * * *', new RedispatchMessage(new UpdateTeam(), 'async')))

            ->add(RecurringMessage::cron('00 8 * * *', new DailyRanking()))

            // Core Bundle Messages (keeping original schedule)
        /*
            ->add(RecurringMessage::cron('00 8 * * 1', new UpdateYoutubeData()))
            ->add(RecurringMessage::cron('00 22 * * * ', new UpdatePlayerBadge()))
            ->add(RecurringMessage::cron('00 6,12,18 * * * ', new PurgeLostPosition()))
            ->add(RecurringMessage::cron('00 6 * * * ', new DesactivateScore()))*/
        ;

        // PN-TWITCH
        //$schedule->add(RecurringMessage::every('5 minutes', new RedispatchMessage(new UpdateStream(), 'async')));

        return $this->schedule ??= $schedule;
    }
}
