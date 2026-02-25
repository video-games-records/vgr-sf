<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;

#[ORM\Entity]
#[ORM\Table(name: 'pnu_refresh_tokens')]
class RefreshToken extends BaseRefreshToken
{
}
