<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service\Registration;

use Atoolo\WebAccount\Dto\Config\EmailConfiguration;
use Atoolo\WebAccount\Dto\Config\RegistrationConfiguration;
use Atoolo\WebAccount\Dto\Config\WebAccountConfiguration;
use Atoolo\WebAccount\Dto\FinishRegistrationRequest;
use Atoolo\WebAccount\Dto\FinishRegistrationResult;
use Atoolo\WebAccount\Exception\EmailAlreadyExistsException;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\Registration\FinishRegistration;
use Atoolo\WebAccount\Service\ServiceException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(FinishRegistration::class)]
class FinishRegistrationTest extends TestCase
{
    private readonly GraphQLClient&MockObject $graphQLClient;

    private readonly ConfigurationLoader&MockObject $configurationLoader;

    private readonly FinishRegistration $finishRegistration;

    private static function createTestConfig(): WebAccountConfiguration
    {
        return new WebAccountConfiguration(
            name: 'test',
            apiKey: 'test-api-key',
            registration: new RegistrationConfiguration(['ROLE_USER', 'ROLE_CUSTOMER']),
            email: new EmailConfiguration(
                theme: 'default',
                from: new Address('noreply@example.com', 'Example'),
                replyTo: new Address('support@example.com', 'Support'),
            ),
        );
    }

    protected function setUp(): void
    {
        $this->graphQLClient = $this->createMock(GraphQLClient::class);
        $this->configurationLoader = $this->createMock(ConfigurationLoader::class);

        $this->finishRegistration = new FinishRegistration(
            $this->graphQLClient,
            $this->configurationLoader,
        );
    }

    /**
     * @throws ServiceException
     * @throws TransportExceptionInterface
     * @throws EmailAlreadyExistsException
     */
    public function testFinishRegistration(): void
    {
        $request = new FinishRegistrationRequest(
            configName: 'test',
            lang: 'en',
            challengeId: 'challenge-id-123',
            code: 123456,
            firstName: 'John',
            lastName: 'Doe',
            password: 'SecurePassword123!',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->expects($this->once())
            ->method('load')
            ->with('test')
            ->willReturn($config);

        $expectedResult = new FinishRegistrationResult(
            id: 'user-id-456',
            email: 'john.doe@example.com',
        );

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation finishUserRegistration'),
                $this->callback(function ($variables) {
                    return $variables['input']['challengeId'] === 'challenge-id-123' &&
                        $variables['input']['code'] === 123456 &&
                        $variables['input']['firstName'] === 'John' &&
                        $variables['input']['lastName'] === 'Doe' &&
                        $variables['input']['password'] === 'SecurePassword123!' &&
                        $variables['input']['roleIdentifiers'] === ['ROLE_USER', 'ROLE_CUSTOMER'] &&
                        $variables['input']['emailParameters']['lang'] === 'en' &&
                        $variables['input']['emailParameters']['theme'] === 'default';
                }),
                'test-api-key',
                $this->callback(function ($responseMapper) {
                    $sampleResponse = [
                        'data' => [
                            'user' => [
                                'finishUserRegistration' => [
                                    '__typename' => 'FinishUserRegistrationResult',
                                    'id' => 'user-id-456',
                                    'email' => 'john.doe@example.com',
                                ],
                            ],
                        ],
                    ];

                    $result = $responseMapper($sampleResponse);

                    $isValid = $result instanceof FinishRegistrationResult &&
                        $result->id === 'user-id-456' &&
                        $result->email === 'john.doe@example.com';

                    // Test unexpected typename case
                    $unexpectedResponse = [
                        'data' => [
                            'user' => [
                                'finishUserRegistration' => [
                                    '__typename' => 'UnexpectedType',
                                    'email' => 'john.doe@example.com',
                                ],
                            ],
                        ],
                    ];
                    try {
                        $responseMapper($unexpectedResponse);
                        return false;
                    } catch (\RuntimeException $e) {
                        return $isValid && str_contains($e->getMessage(), 'Unexpected response type');
                    }
                }),
                $this->callback(function ($errorCallback) {
                    return $errorCallback('', '', []) === null;
                }),
            )
            ->willReturn($expectedResult);

        $result = $this->finishRegistration->finishRegistration($request);

        $this->assertEquals($expectedResult, $result, "The result should match the expected FinishRegistrationResult");
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFinishRegistrationThrowsEmailAlreadyExistsException(): void
    {
        $request = new FinishRegistrationRequest(
            configName: 'test',
            lang: 'en',
            challengeId: 'challenge-id-123',
            code: 123456,
            firstName: 'John',
            lastName: 'Doe',
            password: 'SecurePassword123!',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->method('load')
            ->willReturn($config);

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation finishUserRegistration'),
                $this->anything(),
                'test-api-key',
                $this->callback(function ($responseMapper) {
                    $sampleResponse = [
                        'data' => [
                            'user' => [
                                'finishUserRegistration' => [
                                    '__typename' => 'EmailAlreadyExistsError',
                                    'email' => 'john.doe@example.com',
                                ],
                            ],
                        ],
                    ];

                    try {
                        $responseMapper($sampleResponse);
                        return false;
                    } catch (EmailAlreadyExistsException $e) {
                        return $e->email === 'john.doe@example.com';
                    }
                }),
                $this->anything(),
            )
            ->willThrowException(new EmailAlreadyExistsException('john.doe@example.com'));

        $this->expectException(EmailAlreadyExistsException::class);

        $this->finishRegistration->finishRegistration($request);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFinishRegistrationThrowsServiceException(): void
    {
        $request = new FinishRegistrationRequest(
            configName: 'test',
            lang: 'en',
            challengeId: 'challenge-id-123',
            code: 123456,
            firstName: 'John',
            lastName: 'Doe',
            password: 'SecurePassword123!',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->method('load')
            ->willReturn($config);

        $this->graphQLClient
            ->method('request')
            ->willThrowException(new ServiceException('Finish user registration failed'));

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation finishUserRegistration'),
                $this->anything(),
                'test-api-key',
                $this->anything(),
                $this->callback(function ($errorCallback) {
                    $result = $errorCallback(
                        'FinishUserRegistrationException',
                        'Finish user registration failed',
                        [],
                    );
                    return $result instanceof ServiceException;
                }),
            );

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Finish user registration failed');

        $this->finishRegistration->finishRegistration($request);
    }
}
