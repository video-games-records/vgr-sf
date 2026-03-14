<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Application\Service;

use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Domain\Entity\TopicUserLastVisit;
use App\BoundedContext\Forum\Domain\Entity\ForumUserLastVisit;

class TopicReadService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Marque un topic comme lu pour un utilisateur
     *
     * @param User $user L'utilisateur
     * @param Topic $topic Le topic à marquer comme lu
     * @param bool $flush Si true, fait le flush automatiquement
     * @return array<string, mixed> Informations sur l'opération
     */
    public function markTopicAsRead(User $user, Topic $topic, bool $flush = true): array
    {
        $now = new \DateTime();
        $forum = $topic->getForum();

        // 1. Vérifier si le topic est déjà lu
        $topicVisit = $this->em->getRepository(TopicUserLastVisit::class)
            ->findOneBy(['user' => $user, 'topic' => $topic]);

        $wasAlreadyRead = false;
        if ($topicVisit && $topic->getLastMessage()) {
            $wasAlreadyRead = $topicVisit->getLastVisitedAt() >= $topic->getLastMessage()->getCreatedAt();
        }

        // Si déjà lu, pas besoin de continuer
        if ($wasAlreadyRead) {
            return [
                'topicMarkedAsRead' => false,
                'forumMarkedAsRead' => false,
                'wasAlreadyRead' => true
            ];
        }

        // 2. Mettre à jour ou créer la visite du topic
        if ($topicVisit) {
            $topicVisit->setLastVisitedAt($now);
        } else {
            $topicVisit = new TopicUserLastVisit();
            $topicVisit->setUser($user);
            $topicVisit->setTopic($topic);
            $topicVisit->setLastVisitedAt($now);
            $this->em->persist($topicVisit);
        }

        // 3. Vérifier si tous les topics du forum sont maintenant lus
        // On exclut le topic courant : sa visite n'est pas encore flushée mais on sait qu'il est lu
        $unreadTopicsCount = $this->countUnreadTopicsInForum($user, $forum, $topic);
        $forumMarkedAsRead = false;

        // 4. Si aucun topic non lu, marquer le forum comme lu
        if ($unreadTopicsCount === 0) {
            $forumVisit = $this->em->getRepository(ForumUserLastVisit::class)
                ->findOneBy(['user' => $user, 'forum' => $forum]);

            if ($forumVisit) {
                $forumVisit->setLastVisitedAt($now);
            } else {
                $forumVisit = new ForumUserLastVisit();
                $forumVisit->setUser($user);
                $forumVisit->setForum($forum);
                $forumVisit->setLastVisitedAt($now);
                $this->em->persist($forumVisit);
            }
            $forumMarkedAsRead = true;
        }

        if ($flush) {
            $this->em->flush();
        }

        return [
            'topicMarkedAsRead' => true,
            'forumMarkedAsRead' => $forumMarkedAsRead,
            'wasAlreadyRead' => false
        ];
    }

    /**
     * Marque tous les topics d'un forum comme lus pour un utilisateur
     */
    public function markForumAsRead(User $user, Forum $forum): void
    {
        $now = new \DateTime();

        // 1. Mettre à jour toutes les visites existantes en masse
        $this->em->createQueryBuilder()
            ->update(TopicUserLastVisit::class, 'tuv')
            ->set('tuv.lastVisitedAt', ':now')
            ->where('tuv.user = :user')
            ->andWhere('tuv.topic IN (
                SELECT t.id FROM App\BoundedContext\Forum\Domain\Entity\Topic t
                WHERE t.forum = :forum
            )')
            ->setParameter('now', $now)
            ->setParameter('user', $user)
            ->setParameter('forum', $forum)
            ->getQuery()
            ->execute();

        // 2. Créer des visites pour les topics jamais visités
        $neverVisitedTopics = $this->em->createQueryBuilder()
            ->select('t')
            ->from(Topic::class, 't')
            ->where('t.forum = :forum')
            ->andWhere('t.lastMessage IS NOT NULL')
            ->andWhere('t.id NOT IN (
                SELECT IDENTITY(tuv.topic)
                FROM App\BoundedContext\Forum\Domain\Entity\TopicUserLastVisit tuv
                WHERE tuv.user = :user
            )')
            ->setParameter('forum', $forum)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        foreach ($neverVisitedTopics as $topic) {
            $visit = new TopicUserLastVisit();
            $visit->setUser($user);
            $visit->setTopic($topic);
            $visit->setLastVisitedAt($now);
            $this->em->persist($visit);
        }

        // 3. Mettre à jour ou créer la visite du forum
        $forumVisit = $this->em->getRepository(ForumUserLastVisit::class)
            ->findOneBy(['user' => $user, 'forum' => $forum]);

        if ($forumVisit) {
            $forumVisit->setLastVisitedAt($now);
        } else {
            $forumVisit = new ForumUserLastVisit();
            $forumVisit->setUser($user);
            $forumVisit->setForum($forum);
            $forumVisit->setLastVisitedAt($now);
            $this->em->persist($forumVisit);
        }

        $this->em->flush();
    }

    /**
     * Compte le nombre de topics non lus dans un forum, en excluant optionnellement un topic
     * (utile quand sa visite est en mémoire mais pas encore flushée)
     *
     * @param mixed $user
     */
    private function countUnreadTopicsInForum($user, Forum $forum, ?Topic $excludedTopic = null): int
    {
        try {
            // Topics visités mais avec nouveaux messages
            $visitedUnreadQb = $this->em->createQueryBuilder()
                ->select('COUNT(t.id)')
                ->from('App\BoundedContext\Forum\Domain\Entity\TopicUserLastVisit', 'tuv')
                ->join('tuv.topic', 't')
                ->join('t.lastMessage', 'lm')
                ->where('t.forum = :forum')
                ->andWhere('tuv.user = :user')
                ->andWhere('lm.createdAt > tuv.lastVisitedAt')
                ->setParameter('forum', $forum)
                ->setParameter('user', $user);

            if ($excludedTopic !== null) {
                $visitedUnreadQb->andWhere('t.id != :excludedTopic')
                    ->setParameter('excludedTopic', $excludedTopic->getId());
            }

            $visitedUnread = (int) $visitedUnreadQb->getQuery()->getSingleScalarResult();

            // Topics jamais visités avec des messages
            $neverVisitedQb = $this->em->createQueryBuilder()
                ->select('COUNT(t.id)')
                ->from('App\BoundedContext\Forum\Domain\Entity\Topic', 't')
                ->where('t.forum = :forum')
                ->andWhere('t.lastMessage IS NOT NULL')
                ->andWhere('t.id NOT IN (
                    SELECT IDENTITY(tuv2.topic)
                    FROM App\BoundedContext\Forum\Domain\Entity\TopicUserLastVisit tuv2
                    WHERE tuv2.user = :user
                )')
                ->setParameter('forum', $forum)
                ->setParameter('user', $user);

            if ($excludedTopic !== null) {
                $neverVisitedQb->andWhere('t.id != :excludedTopic')
                    ->setParameter('excludedTopic', $excludedTopic->getId());
            }

            $neverVisited = (int) $neverVisitedQb->getQuery()->getSingleScalarResult();

            return $visitedUnread + $neverVisited;
        } catch (\Exception) {
            return 0;
        }
    }
}
