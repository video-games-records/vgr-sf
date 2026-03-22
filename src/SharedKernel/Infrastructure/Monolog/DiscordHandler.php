<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordHandler extends AbstractProcessingHandler
{
    private const int MAX_MESSAGE_LENGTH = 1990;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $webhookUrl,
        int|string|Level $level = Level::Error,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    public function isHandling(LogRecord $record): bool
    {
        $exception = $record->context['exception'] ?? null;
        if (
            $exception instanceof HttpExceptionInterface
            && in_array($exception->getStatusCode(), [404, 405])
        ) {
            return false;
        }
        return parent::isHandling($record);
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
