<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Presentation\Form\PersonalInfoFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
class PersonalInfo extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/profile/personal-info', name: 'app_profile_personal_info', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(PersonalInfoFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('profile.personal_info.message.success', [], 'User'));

            return $this->redirectToRoute('app_profile_personal_info');
        }

        return $this->render('@User/profile/personal_info.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
