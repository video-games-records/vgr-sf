<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Presentation\Web\Controller;

use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\Message\Domain\Repository\MessageRepositoryInterface;
use App\BoundedContext\Message\Presentation\Form\ComposeMessageType;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Infrastructure\Persistence\Doctrine\UserRepository;
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
        private readonly UserRepository $userRepository,
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

    #[Route('/messages/compose', name: 'message_compose')]
    public function compose(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $preselectedRecipient = null;
        $formData = [];
        $replyToMessage = null;

        // Handle reply to message
        $replyToId = $request->query->getInt('reply');
        if ($replyToId > 0) {
            $replyToMessage = $this->entityManager->getRepository(Message::class)->find($replyToId);
            if (
                $replyToMessage
                && $replyToMessage->getRecipient()->getId() === $user->getId()
                && $replyToMessage->isReplyable()
            ) {
                $preselectedRecipient = $replyToMessage->getSender();
                $formData['recipient'] = $preselectedRecipient !== null ? (string) $preselectedRecipient->getId() : '';

                // Add "Re:" prefix if not already present
                $originalSubject = $replyToMessage->getObject();
                if (!str_starts_with(strtolower($originalSubject), 're:')) {
                    $formData['object'] = 'Re: ' . $originalSubject;
                } else {
                    $formData['object'] = $originalSubject;
                }
            }
        }

        // Handle direct message to user (if not replying)
        if (!$preselectedRecipient) {
            $toUserId = $request->query->getInt('to');
            if ($toUserId > 0) {
                $preselectedRecipient = $this->userRepository->find($toUserId);
                if ($preselectedRecipient && $preselectedRecipient->getId() !== $user->getId()) {
                    $formData['recipient'] = (string) $preselectedRecipient->getId();
                }
            }
        }

        $form = $this->createForm(ComposeMessageType::class, $formData ?: null, [
            'users_autocomplete_url' => '/api/users/autocomplete',
            'recipient_placeholder' => $this->translator->trans('compose.form.recipient_placeholder', [], 'Message'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<string, mixed> $data */
            $data = $form->getData();

            $recipientId = (int) $data['recipient'];
            $recipient = $this->userRepository->find($recipientId);

            if (!$recipient) {
                $this->addFlash('error', $this->translator->trans('compose.error.recipient_not_found', [], 'Message'));
                return $this->redirectToRoute('message_compose');
            }

            if ($recipient->getId() === $user->getId()) {
                $this->addFlash('error', $this->translator->trans('compose.error.cannot_send_to_self', [], 'Message'));
                return $this->redirectToRoute('message_compose');
            }

            $message = new Message();
            $message->setSender($user);
            $message->setRecipient($recipient);
            $message->setObject($data['object']);
            $message->setMessage($data['message']);

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('compose.success.sent', [], 'Message'));

            return $this->redirectToRoute('message_outbox');
        }

        return $this->render('@Message/message/compose.html.twig', [
            'form' => $form,
            'preselectedRecipient' => $preselectedRecipient,
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

        $sender = (int) $request->query->get('sender', 0);
        if ($sender > 0) {
            $filters['sender'] = $sender;
        }

        $recipient = (int) $request->query->get('recipient', 0);
        if ($recipient > 0) {
            $filters['recipient'] = $recipient;
        }

        return $filters;
    }
}
