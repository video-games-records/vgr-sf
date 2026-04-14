<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordHandler extends AbstractProcessingHandler
{
    private const int MAX_MESSAGE_LENGTH = 1990;
    private const int CACHE_TTL = 3600; // 1 hour in seconds

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $webhookUrl,
        private readonly CacheInterface $cache,
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
        // Generate a unique key for this error based on message and context
        $errorKey = $this->generateErrorKey($record);
        $cacheKey = 'discord_error_' . $errorKey;

        // Check if this error was already sent recently
        $alreadySent = $this->cache->get($cacheKey, function (ItemInterface $item) use ($record) {
            // If not in cache, we'll send the notification
            $item->expiresAfter(self::CACHE_TTL);

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

            return true;
        });
    }

    private function generateErrorKey(LogRecord $record): string
    {
        $message = $record->message;
        $exception = $record->context['exception'] ?? null;

        if ($exception instanceof \Throwable) {
            // Create a key based on exception class, message, file and line
            return md5(
                get_class($exception) .
                $exception->getMessage() .
                $exception->getFile() .
                $exception->getLine()
            );
        }

        // For non-exception errors, use message and level
        return md5($message . $record->level->value);
    }
}
