<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ErrorController extends AbstractController
{
    public function show(Throwable $exception): Response
    {
        $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

        if ($statusCode === 404) {
            return $this->render('@SharedKernel/error404.html.twig', [], new Response('', 404));
        }

        return $this->render('@SharedKernel/error.html.twig', [
            'status_code' => $statusCode,
        ], new Response('', $statusCode));
    }
}
