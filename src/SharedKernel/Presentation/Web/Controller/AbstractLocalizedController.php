<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Abstract controller base for all web controllers that need locale support.
 *
 * All controllers that extend this class will automatically have the /{_locale} prefix
 * added to their routes.
 *
 * IMPORTANT: In Symfony 8, the #[Route] attribute on abstract classes is NOT inherited
 * automatically. You MUST add the #[Route] attribute on each concrete controller class.
 *
 * Usage example:
 * ```php
 * #[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
 * class MyController extends AbstractLocalizedController
 * {
 *     #[Route('/my-page', name: 'my_page')]
 *     public function index(string $_locale): Response
 *     {
 *         // The final route will be: /{_locale}/my-page
 *         // The $_locale parameter will be automatically injected
 *         return $this->render('...');
 *     }
 * }
 * ```
 *
 * @see https://symfony.com/doc/current/routing.html#localized-routes-i18n
 */
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
abstract class AbstractLocalizedController extends AbstractController
{
}
