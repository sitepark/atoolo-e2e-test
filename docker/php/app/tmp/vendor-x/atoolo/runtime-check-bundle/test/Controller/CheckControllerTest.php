<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Controller;

use Atoolo\Runtime\Check\Controller\CheckController;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\FpmFcgi\RuntimeCheck;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CheckController::class)]
class CheckControllerTest extends TestCase
{
    private RuntimeCheck&MockObject $runtimeCheck;

    private CheckController $controller;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->runtimeCheck = $this->createMock(RuntimeCheck::class);
        $this->controller = new CheckController($this->runtimeCheck);
    }

    public function testCheck(): void
    {
        $checkStatus = CheckStatus::createSuccess();
        $checkStatus->addReport('test', [
            'a' => 'b',
        ]);
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(RuntimeType::CLI, $checkStatus);

        $this->runtimeCheck->method('execute')->willReturn(
            $runtimeStatus,
        );

        $request = $this->createMock(Request::class);
        $response = $this->controller->check($request);
        $json = json_decode(
            $response->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $this->assertEquals(
            [
                'success' => true,
                'cli' => [
                    'reports' => [
                        'test' => [
                            'a' => 'b',
                        ],
                    ],
                    'success' => true,
                ],
            ],
            $json,
            'Unexpected response content',
        );
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testCheckWithSkipArray(): void
    {
        $this->runtimeCheck->expects($this->once())
            ->method('execute')
            ->with(
                [
                    RuntimeType::CLI->value,
                ],
            );
        $request = $this->createMock(Request::class);
        $request->method('get')
            ->with('skip')
            ->willReturn(
                [
                    RuntimeType::CLI->value,
                ],
            );

        $this->controller->check($request);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testCheckWithSkipString(): void
    {
        $this->runtimeCheck->expects($this->once())
            ->method('execute')
            ->with([
                RuntimeType::CLI->value,
                RuntimeType::WORKER->value,
            ]);
        $request = $this->createMock(Request::class);
        $request->method('get')
            ->with('skip')
            ->willReturn(
                RuntimeType::CLI->value . ',' . RuntimeType::WORKER->value,
            );

        $this->controller->check($request);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testCheckWithUnsupportedSkipType(): void
    {
        $this->runtimeCheck->expects($this->once())
            ->method('execute')
            ->with([]);
        $request = $this->createMock(Request::class);
        $request->method('get')
            ->with('skip')
            ->willReturn(
                true,
            );

        $this->controller->check($request);
    }
}
