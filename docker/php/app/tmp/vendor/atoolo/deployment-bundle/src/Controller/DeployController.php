<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Controller;

use Atoolo\Deployment\Service\Deployer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeployController extends AbstractController
{
    /**
     */
    public function __construct(
        private readonly Deployer $deployer,
    ) {}

    #[Route('/api/admin/deploy', name: 'atoolo_deployment_deploy')]
    public function deploy(Request $request): Response
    {
        $successful = $this->deployer->execute();
        return new JsonResponse(['success' => $successful]);
    }

    /**
     * @deprecated will be removed when all projects have been converted
     */
    #[Route('/api/admin/warm-up', name: 'atoolo_deployment_warmup')]
    public function warmup(Request $request): Response
    {
        $successful = $this->deployer->execute();
        return new JsonResponse(['success' => $successful]);
    }
}
