<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Security\Voter;

use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\User\Domain\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Topic>
 */
class TopicVoter extends Voter
{
    public const string EDIT = 'TOPIC_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof Topic;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $token->getRoleNames(), true)) {
            return true;
        }

        /** @var Topic $topic */
        $topic = $subject;

        return $topic->getUser()->getId() === $user->getId();
    }
}
