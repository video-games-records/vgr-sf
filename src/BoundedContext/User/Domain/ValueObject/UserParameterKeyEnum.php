<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Domain\ValueObject;

enum UserParameterKeyEnum: string
{
    case SCORE_FORM_PER_PAGE = 'score_form_per_page';
    case HOME_DASHBOARD = 'home_dashboard';

    public function getDefault(): string
    {
        return match ($this) {
            self::SCORE_FORM_PER_PAGE => '20',
            self::HOME_DASHBOARD => 'community',
        };
    }
}
