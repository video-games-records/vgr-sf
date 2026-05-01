<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use App\BoundedContext\User\Application\Service\UserParameterService;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Presentation\Form\UserParametersFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[IsGranted('ROLE_USER')]
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class EditParameters extends AbstractController
{
    public function __construct(
        private readonly UserParameterService $parameterService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/profile/parameters', name: 'app_profile_parameters', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserParametersFormType::class, $this->parameterService->getFormData($user));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<string, string> $data */
            $data = $form->getData();
            $this->parameterService->saveParameters($user, $data);

            $this->addFlash('success', $this->translator->trans('profile.parameters.message.success', [], 'User'));

            return $this->redirectToRoute('app_profile_parameters');
        }

        return $this->render('@User/profile/parameters.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
