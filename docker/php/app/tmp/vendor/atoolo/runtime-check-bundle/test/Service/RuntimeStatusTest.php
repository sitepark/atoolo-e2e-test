<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service;

use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuntimeStatus::class)]
class RuntimeStatusTest extends TestCase
{
    public function testAddStatus(): void
    {
        $checkStatus = CheckStatus::createSuccess()
            ->addReport('test', ['a' => 'b']);
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(RuntimeType::CLI, $checkStatus);

        $this->assertEquals(
            $checkStatus,
            $runtimeStatus->getStatus(RuntimeType::CLI),
            'Status is not as expected',
        );
    }

    public function testGetTypes(): void
    {
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(
            RuntimeType::CLI,
            CheckStatus::createSuccess(),
        );
        $runtimeStatus->addStatus(
            RuntimeType::WORKER,
            CheckStatus::createSuccess(),
        );

        $this->assertEquals(
            [RuntimeType::CLI, RuntimeType::WORKER],
            $runtimeStatus->getTypes(),
            'Unexpected types',
        );
    }

    public function testIsSuccess(): void
    {
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(
            RuntimeType::CLI,
            CheckStatus::createSuccess(),
        );

        $this->assertTrue(
            $runtimeStatus->isSuccess(),
            'Status should be successful',
        );
    }

    public function testIsNotSuccess(): void
    {
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(
            RuntimeType::CLI,
            CheckStatus::createSuccess(),
        );
        $runtimeStatus->addStatus(
            RuntimeType::WORKER,
            CheckStatus::createFailure(),
        );

        $this->assertFalse(
            $runtimeStatus->isSuccess(),
            'Status should not be successful',
        );
    }

    public function testGetMessages(): void
    {
        $checkStatus = CheckStatus::createSuccess()
            ->addMessage('test', 'message');
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(RuntimeType::CLI, $checkStatus);

        $this->assertEquals(
            ['cli/test: message'],
            $runtimeStatus->getMessages(),
            'Messages are not as expected',
        );
    }

    public function testSerialize(): void
    {
        $checkStatus = CheckStatus::createSuccess()
            ->addReport('test', ['a' => 'b'])
            ->addMessage('test', 'message');
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(RuntimeType::CLI, $checkStatus);

        $this->assertEquals(
            [
                'cli' => [
                    'success' => true,
                    'reports' => ['test' => ['a' => 'b']],
                    'messages' => ['test' => ['message']],
                ],
                'success' => true,
                'messages' => ['cli/test: message'],
            ],
            $runtimeStatus->serialize(),
            'Status is not as expected',
        );
    }

    public function testDeserialize(): void
    {
        $data = [
            'cli' => [
                'success' => true,
                'reports' => ['test' => ['a' => 'b']],
                'messages' => ['test' => ['message']],
            ],
            'success' => true,
            'messages' => ['cli/test: message'],
        ];

        $checkStatus = CheckStatus::createSuccess()
            ->addReport('test', ['a' => 'b'])
            ->addMessage('test', 'message');
        $expected = new RuntimeStatus();
        $expected->addStatus(RuntimeType::CLI, $checkStatus);

        $this->assertEquals(
            $expected,
            RuntimeStatus::deserialize($data),
            'Status is not as expected',
        );
    }
}
