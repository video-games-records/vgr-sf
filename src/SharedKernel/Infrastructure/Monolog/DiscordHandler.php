<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordHandler extends AbstractProcessingHandler
{
    private const MAX_MESSAGE_LENGTH = 1990;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $webhookUrl,
        int|string|Level $level = Level::Error,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $content = sprintf(
            "**[%s] %s**\n```%s```",
            $record->level->getName(),
            $record->channel,
            substr($record->formatted ?? $record->message, 0, self::MAX_MESSAGE_LENGTH)
        );

        try {
            $this->httpClient->request('POST', $this->webhookUrl, [
                'json' => ['content' => $content],
            ]);
        } catch (\Throwable) {
            // Silently fail to avoid infinite loop
        }
    }
}
