<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Web\Controller;

use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LatestMessagesController extends AbstractController
{
    public const string CACHE_KEY = 'latest_forum_messages';

    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly CacheInterface $cache,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(int $ttl = 0): Response
    {
        $userRoles = $this->tokenStorage->getToken()?->getRoleNames() ?? [];
        sort($userRoles);

        if ($ttl > 0) {
            $cacheKey = self::CACHE_KEY . '_' . md5(implode(',', $userRoles));
            $html = $this->cache->get($cacheKey, function (ItemInterface $item) use ($ttl, $userRoles) {
                $item->expiresAfter($ttl);
                $messages = $this->messageRepository->findLatest(5, $userRoles);

                return $this->renderView('@Forum/message/_latest_messages.html.twig', [
                    'messages' => $messages,
                ]);
            });

            return new Response($html);
        }

        $messages = $this->messageRepository->findLatest(5, $userRoles);

        return $this->render('@Forum/message/_latest_messages.html.twig', [
            'messages' => $messages,
        ]);
    }
}
