<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestRoute
{
    #[Route('/public-content', name: 'public-content')]
    public function foo(): Response
    {
        return new Response('Public content');
    }

    #[Route('/protected-content', name: 'protected-content')]
    public function bar(): Response
    {
        return new Response('Protected content');
    }

    #[Route('/account', name: 'account')]
    public function account(): Response
    {
        return new Response('Please log in to gain access to protected content');
    }

}
