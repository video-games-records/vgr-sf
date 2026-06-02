<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\EventListener\TeamListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class TeamListenerTest extends TestCase
{
    private function makeListener(HtmlSanitizerInterface $sanitizer): TeamListener
    {
        return new TeamListener($sanitizer);
    }

    public function testPrePersistSanitizesPresentation(): void
    {
        $team = $this->createMock(Team::class);
        $team->method('getPresentation')->willReturn('<p>Some <script>alert(1)</script> content</p>');
        $team->expects($this->once())->method('setPresentation')->with('<p>Some  content</p>');

        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $sanitizer->method('sanitize')->willReturn('<p>Some  content</p>');

        $this->makeListener($sanitizer)->prePersist($team);
    }

    public function testPreUpdateSanitizesPresentation(): void
    {
        $team = $this->createMock(Team::class);
        $team->method('getPresentation')->willReturn('<b>Bold</b>');
        $team->expects($this->once())->method('setPresentation')->with('<b>Bold</b>');

        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $sanitizer->method('sanitize')->willReturn('<b>Bold</b>');

        $this->makeListener($sanitizer)->preUpdate($team);
    }

    public function testPrePersistSkipsSanitizationWhenPresentationIsNull(): void
    {
        $team = $this->createMock(Team::class);
        $team->method('getPresentation')->willReturn(null);
        $team->expects($this->never())->method('setPresentation');

        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $sanitizer->expects($this->never())->method('sanitize');

        $this->makeListener($sanitizer)->prePersist($team);
    }

    public function testPreUpdateSkipsSanitizationWhenPresentationIsNull(): void
    {
        $team = $this->createMock(Team::class);
        $team->method('getPresentation')->willReturn(null);
        $team->expects($this->never())->method('setPresentation');

        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $sanitizer->expects($this->never())->method('sanitize');

        $this->makeListener($sanitizer)->preUpdate($team);
    }
}
