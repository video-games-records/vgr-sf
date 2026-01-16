<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Client\Endpoint;

use GuzzleHttp\Client;
use KrisKuiper\IGDBV4\Contracts\AccessConfigInterface;
use KrisKuiper\IGDBV4\Endpoints\AbstractEndpoint;

class PlatformTypeEndpoint extends AbstractEndpoint
{
    public const string ENDPOINT = 'platform_types';

    public function __construct(Client $client, AccessConfigInterface $config)
    {
        parent::__construct($client, $config);
    }

    public function getEndpoint(): string
    {
        return self::ENDPOINT;
    }
}
