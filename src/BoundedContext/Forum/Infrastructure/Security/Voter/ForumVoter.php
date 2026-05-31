<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Security\Voter;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\ValueObject\ForumStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Forum>
 */
class ForumVoter extends Voter
{
    public const string VIEW = 'FORUM_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Forum;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Forum $forum */
        $forum = $subject;

        // Forum public sans rôle requis → tout le monde
        if ($forum->getStatus() === ForumStatus::PUBLIC) {
            return true;
        }

        // Forum privé → doit être authentifié
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }

        // Forum privé avec rôle requis → vérifier que l'utilisateur a ce rôle
        if ($forum->getRole() !== null) {
            return in_array($forum->getRole(), $token->getRoleNames(), true);
        }

        // Forum privé sans rôle spécifique → authentification suffisante
        return true;
    }
}
