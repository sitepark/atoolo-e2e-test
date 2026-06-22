<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\GraphQL;

use Atoolo\WebAccount\Dto\StartPasswordRecoveryResult;
use Atoolo\WebAccount\GraphQL\Input\FinishPasswordRecoveryInput;
use Atoolo\WebAccount\GraphQL\Input\StartPasswordRecoveryInput;
use Atoolo\WebAccount\GraphQL\Password;
use Atoolo\WebAccount\Service\Password\FinishPasswordRecovery;
use Atoolo\WebAccount\Service\Password\StartPasswordRecovery;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(Password::class)]
class PasswordTest extends TestCase
{
    private readonly StartPasswordRecovery&MockObject $startPasswordRecovery;

    private readonly FinishPasswordRecovery&MockObject $finishPasswordRecovery;

    private readonly Password $password;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->startPasswordRecovery = $this->createMock(StartPasswordRecovery::class);
        $this->finishPasswordRecovery = $this->createMock(FinishPasswordRecovery::class);

        $this->password = new Password(
            $this->startPasswordRecovery,
            $this->finishPasswordRecovery,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testStartPasswordRecovery(): void
    {
        $input = new StartPasswordRecoveryInput();
        $input->configName = 'default';
        $input->lang = 'en';
        $input->username = 'johndoe';

        $this->startPasswordRecovery
            ->expects($this->once())
            ->method('startPasswordRecovery')
            ->with($this->callback(function ($request) use ($input) {
                return $request->configName === $input->configName &&
                    $request->lang === $input->lang &&
                    $request->username === $input->username;
            }))
            ->willReturn(new StartPasswordRecoveryResult(
                challengeId: 'challenge-id-123',
                createAt: new DateTime('09-01-2026 08:06'),
                expiresAt: new DateTime('09-01-2026 08:36'),
            ));

        $result = $this->password->startPasswordRecovery($input);
        $expected = new StartPasswordRecoveryResult(
            challengeId: 'challenge-id-123',
            createAt: new DateTime('09-01-2026 08:06'),
            expiresAt: new DateTime('09-01-2026 08:36'),
        );

        $this->assertEquals($expected, $result, "The result should match the expected StartPasswordRecoveryResult.");
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFinishPasswordRecovery(): void
    {
        $input = new FinishPasswordRecoveryInput();
        $input->configName = 'default';
        $input->lang = 'en';
        $input->challengeId = 'challenge-id-123';
        $input->code = 123456;
        $input->newPassword = 'newSecurePassword!';
        $this->finishPasswordRecovery
            ->expects($this->once())
            ->method('finishPasswordRecovery')
            ->with($this->callback(function ($request) use ($input) {
                return $request->configName === $input->configName &&
                    $request->lang === $input->lang &&
                    $request->challengeId === $input->challengeId &&
                    $request->code === $input->code &&
                    $request->newPassword === $input->newPassword;
            }));
        $result = $this->password->finishPasswordRecovery($input);
        $this->assertTrue($result, "The finishPasswordRecovery method should return true.");
    }
}
