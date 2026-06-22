<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\GraphQL;

use Atoolo\WebAccount\Dto\EmailAlreadyExistsError;
use Atoolo\WebAccount\Dto\FinishRegistrationResult;
use Atoolo\WebAccount\Dto\StartRegistrationResult;
use Atoolo\WebAccount\Exception\EmailAlreadyExistsException;
use Atoolo\WebAccount\GraphQL\Input\FinishRegistrationInput;
use Atoolo\WebAccount\GraphQL\Input\StartRegistrationInput;
use Atoolo\WebAccount\GraphQL\Registration;
use Atoolo\WebAccount\Service\Registration\FinishRegistration;
use Atoolo\WebAccount\Service\Registration\StartRegistration;
use DateTime;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Overblog\GraphQLBundle\Resolver\UnresolvableException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(Registration::class)]
class RegistrationTest extends TestCase
{
    private readonly StartRegistration&MockObject $startRegistration;

    private readonly FinishRegistration&MockObject $finishRegistration;

    private readonly Registration $registration;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->startRegistration = $this->createMock(StartRegistration::class);
        $this->finishRegistration = $this->createMock(FinishRegistration::class);

        $this->registration = new Registration(
            $this->startRegistration,
            $this->finishRegistration,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testStartRegistration(): void
    {
        $input = new StartRegistrationInput();
        $input->configName = 'default';
        $input->lang = 'en';
        $input->emailAddress = 'john.doe@example.com';

        $this->startRegistration
            ->expects($this->once())
            ->method('startRegistration')
            ->with($this->callback(function ($request) use ($input) {
                return $request->configName === $input->configName &&
                    $request->lang === $input->lang &&
                    $request->emailAddress === $input->emailAddress;
            }))
            ->willReturn(new StartRegistrationResult(
                challengeId: 'challenge-id-123',
                createAt: new DateTime('09-01-2026 08:06'),
                expiresAt: new DateTime('09-01-2026 08:36'),
            ));

        $result = $this->registration->startRegistration($input);
        $expected = new StartRegistrationResult(
            challengeId: 'challenge-id-123',
            createAt: new DateTime('09-01-2026 08:06'),
            expiresAt: new DateTime('09-01-2026 08:36'),
        );

        $this->assertEquals($expected, $result, "The result should match the expected StartRegistrationResult.");
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFinishRegistration(): void
    {
        $input = new FinishRegistrationInput();
        $input->configName = 'default';
        $input->lang = 'en';
        $input->challengeId = 'challenge-id-123';
        $input->code = 123456;
        $input->firstName = 'John';
        $input->lastName = 'Doe';
        $input->password = 'SecurePassword123!';

        $this->finishRegistration
            ->expects($this->once())
            ->method('finishRegistration')
            ->with($this->callback(function ($request) use ($input) {
                return $request->configName === $input->configName &&
                    $request->lang === $input->lang &&
                    $request->challengeId === $input->challengeId &&
                    $request->code === $input->code &&
                    $request->firstName === $input->firstName &&
                    $request->lastName === $input->lastName &&
                    $request->password === $input->password;
            }))
            ->willReturn(new FinishRegistrationResult(
                id: 'user-id-456',
                email: 'john.doe@example.com',
            ));

        $result = $this->registration->finishRegistration($input);
        $expected = new FinishRegistrationResult(
            id: 'user-id-456',
            email: 'john.doe@example.com',
        );

        $this->assertEquals($expected, $result, "The result should match the expected FinishRegistrationResult.");
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFinishRegistrationWithEmailAlreadyExists(): void
    {
        $input = new FinishRegistrationInput();
        $input->configName = 'default';
        $input->lang = 'en';
        $input->challengeId = 'challenge-id-123';
        $input->code = 123456;
        $input->firstName = 'John';
        $input->lastName = 'Doe';
        $input->password = 'SecurePassword123!';

        $this->finishRegistration
            ->expects($this->once())
            ->method('finishRegistration')
            ->with($this->callback(function ($request) use ($input) {
                return $request->configName === $input->configName &&
                    $request->lang === $input->lang &&
                    $request->challengeId === $input->challengeId &&
                    $request->code === $input->code &&
                    $request->firstName === $input->firstName &&
                    $request->lastName === $input->lastName &&
                    $request->password === $input->password;
            }))
            ->willThrowException(new EmailAlreadyExistsException('john.doe@example.com'));

        $result = $this->registration->finishRegistration($input);
        $expected = new EmailAlreadyExistsError(email: 'john.doe@example.com');

        $this->assertEquals($expected, $result, "The result should be an EmailAlreadyExistsError when the email already exists.");
    }

    public function testMapResponseTypeForFinishRegistrationResult(): void
    {
        $typeResolver = $this->createMock(TypeResolver::class);
        $typeResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('FinishRegistrationResult');

        $result = new FinishRegistrationResult(
            id: 'user-id-456',
            email: 'john.doe@example.com',
        );

        $this->registration->mapResponseType($result, $typeResolver);
    }

    public function testMapResponseTypeForEmailAlreadyExistsError(): void
    {
        $typeResolver = $this->createMock(TypeResolver::class);
        $typeResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('EmailAlreadyExistsError');

        $error = new EmailAlreadyExistsError(email: 'john.doe@example.com');

        $this->registration->mapResponseType($error, $typeResolver);
    }

    public function testMapResponseTypeThrowsUnresolvableException(): void
    {
        $typeResolver = $this->createMock(TypeResolver::class);

        $this->expectException(UnresolvableException::class);
        $this->expectExceptionMessage("Couldn't resolve type for union 'ResetPasswordResponse'");

        $this->registration->mapResponseType(new \stdClass(), $typeResolver);
    }

    public function testGetAliases(): void
    {
        $aliases = Registration::getAliases();
        $expected = ['mapResponseType' => 'map_response_type'];

        $this->assertEquals($expected, $aliases, "The getAliases method should return the correct alias mapping.");
    }
}
