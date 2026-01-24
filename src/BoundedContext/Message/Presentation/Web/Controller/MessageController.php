<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Presentation\Web\Controller;

use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\Message\Domain\Repository\MessageRepositoryInterface;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
#[IsGranted('ROLE_USER')]
class MessageController extends AbstractLocalizedController
{
    private const int MESSAGES_PER_PAGE = 20;

    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/messages/inbox', name: 'message_inbox')]
    public function inbox(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $page = max(1, $request->query->getInt('page', 1));
        $filters = $this->buildFilters($request);

        $queryBuilder = $this->messageRepository->getInboxMessages($user, $filters);
        $paginator = new Paginator($queryBuilder);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::MESSAGES_PER_PAGE)
            ->setMaxResults(self::MESSAGES_PER_PAGE);

        $totalMessages = count($paginator);
        $totalPages = (int) ceil($totalMessages / self::MESSAGES_PER_PAGE);

        $senders = $this->messageRepository->getSenders($user);
        $nbNewMessages = $this->messageRepository->getNbNewMessage($user);

        return $this->render('@Message/message/inbox.html.twig', [
            'messages' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalMessages' => $totalMessages,
            'filters' => $filters,
            'senders' => $senders,
            'nbNewMessages' => $nbNewMessages,
        ]);
    }

    #[Route('/messages/outbox', name: 'message_outbox')]
    public function outbox(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $page = max(1, $request->query->getInt('page', 1));
        $filters = $this->buildFilters($request);

        $queryBuilder = $this->messageRepository->getOutboxMessages($user, $filters);
        $paginator = new Paginator($queryBuilder);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::MESSAGES_PER_PAGE)
            ->setMaxResults(self::MESSAGES_PER_PAGE);

        $totalMessages = count($paginator);
        $totalPages = (int) ceil($totalMessages / self::MESSAGES_PER_PAGE);

        $recipients = $this->messageRepository->getRecipients($user);

        return $this->render('@Message/message/outbox.html.twig', [
            'messages' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalMessages' => $totalMessages,
            'filters' => $filters,
            'recipients' => $recipients,
        ]);
    }

    #[Route('/messages/{id}', name: 'message_view', requirements: ['id' => '\d+'])]
    public function view(Message $message): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($message->getRecipient()->getId() !== $user->getId() && $message->getSender()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You are not allowed to view this message.');
        }

        if ($message->getRecipient()->getId() === $user->getId() && !$message->getIsOpened()) {
            $message->setIsOpened(true);
            $this->entityManager->flush();
        }

        $isInbox = $message->getRecipient()->getId() === $user->getId();

        return $this->render('@Message/message/view.html.twig', [
            'message' => $message,
            'isInbox' => $isInbox,
        ]);
    }

    #[Route('/messages/{id}/delete', name: 'message_delete', methods: ['POST'])]
    public function delete(Message $message, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (
            !$this->isCsrfTokenValid(
                'delete-message-' . $message->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $isRecipient = $message->getRecipient()->getId() === $user->getId();
        $isSender = $message->getSender()?->getId() === $user->getId();

        if (!$isRecipient && !$isSender) {
            throw $this->createAccessDeniedException('You are not allowed to delete this message.');
        }

        if ($isRecipient) {
            $message->setIsDeletedRecipient(true);
            $redirect = 'message_inbox';
        } else {
            $message->setIsDeletedSender(true);
            $redirect = 'message_outbox';
        }

        $this->addFlash('success', $this->translator->trans('message.flash.deleted', [], 'Message'));

        $this->entityManager->flush();

        return $this->redirectToRoute($redirect);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFilters(Request $request): array
    {
        $filters = [];

        if ($search = $request->query->get('search')) {
            $filters['search'] = $search;
        }

        if ($isOpened = $request->query->get('isOpened')) {
            $filters['isOpened'] = $isOpened === '1';
        }

        if ($sender = $request->query->getInt('sender')) {
            $filters['sender'] = $sender;
        }

        if ($recipient = $request->query->getInt('recipient')) {
            $filters['recipient'] = $recipient;
        }

        return $filters;
    }
}
