<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service\Registration;

use Atoolo\WebAccount\Dto\Config\EmailConfiguration;
use Atoolo\WebAccount\Dto\Config\RegistrationConfiguration;
use Atoolo\WebAccount\Dto\Config\WebAccountConfiguration;
use Atoolo\WebAccount\Dto\StartRegistrationRequest;
use Atoolo\WebAccount\Dto\StartRegistrationResult;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\Registration\StartRegistration;
use Atoolo\WebAccount\Service\ServiceException;
use DateTime;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(StartRegistration::class)]
class StartRegistrationTest extends TestCase
{
    private readonly GraphQLClient&MockObject $graphQLClient;

    private readonly ConfigurationLoader&MockObject $configurationLoader;

    private readonly StartRegistration $startRegistration;

    private static function createTestConfig(): WebAccountConfiguration
    {
        return new WebAccountConfiguration(
            name: 'test',
            apiKey: 'test-api-key',
            registration: new RegistrationConfiguration(['ROLE_USER']),
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

        $this->startRegistration = new StartRegistration(
            $this->graphQLClient,
            $this->configurationLoader,
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServiceException
     * @throws Exception
     */
    public function testStartRegistration(): void
    {
        $request = new StartRegistrationRequest(
            configName: 'test',
            lang: 'en',
            emailAddress: 'john.doe@example.com',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->expects($this->once())
            ->method('load')
            ->with('test')
            ->willReturn($config);

        $expectedResult = new StartRegistrationResult(
            challengeId: 'challenge-id-123',
            createAt: new DateTime('09-01-2026 08:06'),
            expiresAt: new DateTime('09-01-2026 08:36'),
        );

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation startUserRegistration'),
                $this->callback(function ($variables) {
                    return $variables['input']['email'] === 'john.doe@example.com' &&
                        $variables['input']['emailParameters']['lang'] === 'en' &&
                        $variables['input']['emailParameters']['theme'] === 'default';
                }),
                'test-api-key',
                $this->callback(function ($responseMapper) {
                    $sampleResponse = [
                        'data' => [
                            'user' => [
                                'startUserRegistration' => [
                                    'challengeId' => 'challenge-id-123',
                                    'createdAt' => '2026-01-09T08:06:00+00:00',
                                    'expiresAt' => '2026-01-09T08:36:00+00:00',
                                ],
                            ],
                        ],
                    ];

                    $result = $responseMapper($sampleResponse);

                    return $result instanceof StartRegistrationResult &&
                        $result->challengeId === 'challenge-id-123' &&
                        $result->createAt == new DateTime('2026-01-09T08:06:00+00:00') &&
                        $result->expiresAt == new DateTime('2026-01-09T08:36:00+00:00');
                }),
                $this->callback(function ($errorCallback) {
                    return $errorCallback('', '', []) === null;
                }),
            )
            ->willReturn($expectedResult);

        $result = $this->startRegistration->startRegistration($request);

        $this->assertEquals($expectedResult, $result, "The result should match the expected StartRegistrationResult");
    }

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function testStartRegistrationThrowsServiceException(): void
    {
        $request = new StartRegistrationRequest(
            configName: 'test',
            lang: 'en',
            emailAddress: 'john.doe@example.com',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->method('load')
            ->willReturn($config);

        $this->graphQLClient
            ->method('request')
            ->willThrowException(new ServiceException('Start user registration failed'));

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation startUserRegistration'),
                $this->callback(function ($variables) {
                    return $variables['input']['email'] === 'john.doe@example.com' &&
                        $variables['input']['emailParameters']['lang'] === 'en' &&
                        $variables['input']['emailParameters']['theme'] === 'default';
                }),
                'test-api-key',
                $this->anything(),
                $this->callback(function ($errorCallback) {
                    $result = $errorCallback(
                        'StartUserRegistrationException',
                        'Start user registration failed',
                        [],
                    );
                    return $result instanceof ServiceException;
                }),
            );

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Start user registration failed');

        $this->startRegistration->startRegistration($request);
    }
}
