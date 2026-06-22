<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ApplyCookieJarListener
{
    public function __construct(private readonly CookieJar $cookieJar) {}

    #[AsEventListener(event: 'kernel.response')]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        foreach ($this->cookieJar->all() as $cookie) {
            $response->headers->setCookie($cookie);
        }
    }
}
