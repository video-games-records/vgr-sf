<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Traits;

trait NumberFormatTrait
{
    /**
     * @param $value
     * @return string
     */
    private function numberFormat(int|float $value): string
    {
        return number_format($value);
    }
}
