<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service\Password;

use Atoolo\WebAccount\Dto\Config\EmailConfiguration;
use Atoolo\WebAccount\Dto\Config\RegistrationConfiguration;
use Atoolo\WebAccount\Dto\Config\WebAccountConfiguration;
use Atoolo\WebAccount\Dto\StartPasswordRecoveryRequest;
use Atoolo\WebAccount\Dto\StartPasswordRecoveryResult;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\Password\StartPasswordRecovery;
use Atoolo\WebAccount\Service\ServiceException;
use DateTime;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(StartPasswordRecovery::class)]
class StartPasswordRecoveryTest extends TestCase
{
    private readonly GraphQLClient&MockObject $graphQLClient;

    private readonly ConfigurationLoader&MockObject $configurationLoader;

    private readonly StartPasswordRecovery $startPasswordRecovery;

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

        $this->startPasswordRecovery = new StartPasswordRecovery(
            $this->graphQLClient,
            $this->configurationLoader,
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function testStartPasswordRecovery(): void
    {
        $request = new StartPasswordRecoveryRequest(
            configName: 'test',
            lang: 'en',
            username: 'johndoe',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->expects($this->once())
            ->method('load')
            ->with('test')
            ->willReturn($config);

        $expectedResult = new StartPasswordRecoveryResult(
            challengeId: 'challenge-id-123',
            createAt: new DateTime('09-01-2026 08:06'),
            expiresAt: new DateTime('09-01-2026 08:36'),
        );

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation startPasswordRecovery'),
                $this->callback(function ($variables) {
                    return $variables['input']['username'] === 'johndoe' &&
                        $variables['input']['emailParameters']['lang'] === 'en' &&
                        $variables['input']['emailParameters']['theme'] === 'default';
                }),
                'test-api-key',
                $this->callback(function ($responseMapper) {
                    $sampleResponse = [
                        'data' => [
                            'security' => [
                                'password' => [
                                    'startPasswordRecovery' => [
                                        'challengeId' => 'challenge-id-123',
                                        'createdAt' => '2026-01-09T08:06:00+00:00',
                                        'expiresAt' => '2026-01-09T08:36:00+00:00',
                                    ],
                                ],
                            ],
                        ],
                    ];

                    $result = $responseMapper($sampleResponse);

                    return $result instanceof StartPasswordRecoveryResult &&
                        $result->challengeId === 'challenge-id-123' &&
                        $result->createAt == new DateTime('2026-01-09T08:06:00+00:00') &&
                        $result->expiresAt == new DateTime('2026-01-09T08:36:00+00:00');
                }),
                $this->callback(function ($errorCallback) {
                    return $errorCallback("", "", []) === null;
                }),
            )
            ->willReturn($expectedResult);

        $result = $this->startPasswordRecovery->startPasswordRecovery($request);

        $this->assertEquals(
            $expectedResult,
            $result,
            "The result should match the expected StartPasswordRecoveryResult",
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function testStartPasswordRecoveryThrowsServiceException(): void
    {
        $request = new StartPasswordRecoveryRequest(
            configName: 'test',
            lang: 'en',
            username: 'johndoe',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->method('load')
            ->willReturn($config);

        $this->graphQLClient
            ->method('request')
            ->willThrowException(new ServiceException('Start password recovery failed'));

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation startPasswordRecovery'),
                $this->callback(function ($variables) {
                    return $variables['input']['username'] === 'johndoe' &&
                        $variables['input']['emailParameters']['lang'] === 'en' &&
                        $variables['input']['emailParameters']['theme'] === 'default';
                }),
                'test-api-key',
                $this->anything(),
                $this->callback(function ($errorCallback) {

                    $result = $errorCallback(
                        'StartPasswordRecoveryException',
                        'An error occurred',
                        [],
                    );

                    return $result instanceof ServiceException;
                }),
            );

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Start password recovery failed');

        $this->startPasswordRecovery->startPasswordRecovery($request);
    }
}
