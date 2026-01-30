<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\ApiPlatform\Country;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Country\CountryDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\CountryMapper;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\CountryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class CountryDataProvider implements ProviderInterface
{
    public function __construct(
        private CountryRepository $countryRepository,
        private CountryMapper $countryMapper
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CountryDTO
    {
        $country = $this->countryRepository->find($uriVariables['id']);

        if (!$country) {
            throw new NotFoundHttpException('Country not found');
        }

        return $this->countryMapper->toDTO($country);
    }
}
