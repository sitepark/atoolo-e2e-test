<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Security;

use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

#[AsAlias(id: 'atoolo_web_account.unauthorized_entry_point')]
class UnauthorizedEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        #[Autowire('%atoolo_web_account.unauthorized_entry_point%')]
        private readonly string $entryPoint,
    ) {}

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->entryPoint);
    }
}
