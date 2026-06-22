<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Test\Controller;

use Atoolo\Deployment\Controller\DeployController;
use Atoolo\Deployment\Service\Deployer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(DeployController::class)]
class DeployControllerTest extends TestCase
{
    private Deployer&MockObject $deployer;
    private DeployController $controller;

    public function setUp(): void
    {
        $this->deployer = $this->createMock(Deployer::class);
        $this->controller = new DeployController($this->deployer);
    }

    public function testDeploy(): void
    {
        $request = $this->createMock(Request::class);
        $this->deployer
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->controller->deploy($request);
    }

    public function testWarmUp(): void
    {
        $request = $this->createMock(Request::class);
        $this->deployer
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->controller->warmup($request);
    }
}
