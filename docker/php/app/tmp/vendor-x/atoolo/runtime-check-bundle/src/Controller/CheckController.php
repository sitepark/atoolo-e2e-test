<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Controller;

use Atoolo\Runtime\Check\Service\FpmFcgi\RuntimeCheck;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CheckController extends AbstractController
{
    public function __construct(
        private readonly RuntimeCheck $runtimeCheck,
    ) {}

    /**
     * @throws JsonException
     */
    #[Route('/api/runtime-check', name: 'atoolo_runtime_check')]
    #[IsGranted(
        attribute: new Expression(
            'is_granted("ROLE_SYSTEM_AUDITOR") '
            . 'or "127.0.0.1" == subject.getClientIp()',
        ),
        subject: new Expression('request'),
    )]
    public function check(Request $request): Response
    {
        $skip = $request->get('skip') ?? [];
        if (is_string($skip)) {
            $skip = explode(',', $skip);
        } elseif (!is_array($skip)) {
            $skip = [];
        }

        $runtimeStatus = $this->runtimeCheck->execute($skip);

        $result = $runtimeStatus->serialize();
        $success = $runtimeStatus->isSuccess();

        $res = new JsonResponse($result);
        $res->setStatusCode($success ? 200 : 500);
        return $res;
    }
}
