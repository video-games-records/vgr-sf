<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Api\Controller;

use App\BoundedContext\User\Infrastructure\Persistence\Doctrine\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class Autocomplete extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/api/users/autocomplete', name: 'api_user_autocomplete', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $q = (string) $request->query->get('query', '');

        $users = $this->userRepository->autocomplete($q);

        $results = array_map(fn($user) => [
            'id' => $user->getId(),
            'text' => $user->getUsername(),
        ], $users);

        return $this->json($results);
    }
}
