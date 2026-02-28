<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Web\Controller;

use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LatestMessagesController extends AbstractController
{
    public const string CACHE_KEY = 'latest_forum_messages';

    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly CacheInterface $cache,
    ) {
    }

    public function __invoke(int $ttl = 0): Response
    {
        if ($ttl > 0) {
            $html = $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) use ($ttl) {
                $item->expiresAfter($ttl);
                $messages = $this->messageRepository->findLatest(5);

                return $this->renderView('@Forum/message/_latest_messages.html.twig', [
                    'messages' => $messages,
                ]);
            });

            return new Response($html);
        }

        $messages = $this->messageRepository->findLatest(5);

        return $this->render('@Forum/message/_latest_messages.html.twig', [
            'messages' => $messages,
        ]);
    }
}
