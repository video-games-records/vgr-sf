<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Domain\ValueObject;

use App\BoundedContext\User\Domain\ValueObject\UserParameterKeyEnum;
use PHPUnit\Framework\TestCase;

class UserParameterKeyEnumTest extends TestCase
{
    public function testGetDefaultForScoreFormPerPage(): void
    {
        $this->assertSame('20', UserParameterKeyEnum::SCORE_FORM_PER_PAGE->getDefault());
    }

    public function testGetDefaultForHomeDashboard(): void
    {
        $this->assertSame('community', UserParameterKeyEnum::HOME_DASHBOARD->getDefault());
    }

    public function testEnumValues(): void
    {
        $this->assertSame('score_form_per_page', UserParameterKeyEnum::SCORE_FORM_PER_PAGE->value);
        $this->assertSame('home_dashboard', UserParameterKeyEnum::HOME_DASHBOARD->value);
    }

    public function testCasesCountIsTwo(): void
    {
        $this->assertCount(2, UserParameterKeyEnum::cases());
    }
}
