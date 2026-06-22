<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service\Password;

use Atoolo\WebAccount\Dto\Config\EmailConfiguration;
use Atoolo\WebAccount\Dto\Config\RegistrationConfiguration;
use Atoolo\WebAccount\Dto\Config\WebAccountConfiguration;
use Atoolo\WebAccount\Dto\FinishPasswordRecoveryRequest;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\Password\FinishPasswordRecovery;
use Atoolo\WebAccount\Service\ServiceException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(FinishPasswordRecovery::class)]
class FinishPasswordRecoveryTest extends TestCase
{
    private readonly GraphQLClient&MockObject $graphQLClient;

    private readonly ConfigurationLoader&MockObject $configurationLoader;

    private readonly FinishPasswordRecovery $finishPasswordRecovery;

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

        $this->finishPasswordRecovery = new FinishPasswordRecovery(
            $this->graphQLClient,
            $this->configurationLoader,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFinishPasswordRecovery(): void
    {
        $request = new FinishPasswordRecoveryRequest(
            configName: 'test',
            lang: 'en',
            challengeId: 'challenge-id-123',
            code: 123456,
            newPassword: 'NewSecurePassword123!',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->expects($this->once())
            ->method('load')
            ->with('test')
            ->willReturn($config);

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation finishPasswordRecovery'),
                $this->callback(function ($variables) {
                    return $variables['input']['challengeId'] === 'challenge-id-123' &&
                        $variables['input']['code'] === 123456 &&
                        $variables['input']['newPassword'] === 'NewSecurePassword123!' &&
                        $variables['input']['emailParameters']['lang'] === 'en' &&
                        $variables['input']['emailParameters']['theme'] === 'default';
                }),
                'test-api-key',
                $this->callback(function ($successChecker) {
                    $sampleResponse = [
                        'data' => [
                            'security' => [
                                'password' => [
                                    'finishPasswordRecovery' => true,
                                ],
                            ],
                        ],
                    ];
                    return $successChecker($sampleResponse) === true;
                }),
                $this->callback(function ($errorCallback) {
                    return $errorCallback('', '', []) === null;
                }),
            );

        $this->finishPasswordRecovery->finishPasswordRecovery($request);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testFinishPasswordRecoveryThrowsServiceException(): void
    {
        $request = new FinishPasswordRecoveryRequest(
            configName: 'test',
            lang: 'en',
            challengeId: 'challenge-id-123',
            code: 123456,
            newPassword: 'NewSecurePassword123!',
        );

        $config = self::createTestConfig();

        $this->configurationLoader
            ->method('load')
            ->willReturn($config);

        $this->graphQLClient
            ->method('request')
            ->willThrowException(new ServiceException('Finish password recovery failed'));

        $this->graphQLClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('mutation finishPasswordRecovery'),
                $this->callback(function ($variables) {
                    return $variables['input']['challengeId'] === 'challenge-id-123' &&
                        $variables['input']['code'] === 123456 &&
                        $variables['input']['newPassword'] === 'NewSecurePassword123!';
                }),
                'test-api-key',
                $this->anything(),
                $this->callback(function ($errorCallback) {
                    $result1 = $errorCallback(
                        'CodeVerificationFailedException',
                        'Code verification failed',
                        [],
                    );
                    $result2 = $errorCallback(
                        'FinishPasswordRecoveryException',
                        'Finish password recovery failed',
                        [],
                    );
                    return $result1 instanceof ServiceException && $result2 instanceof ServiceException;
                }),
            );

        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Finish password recovery failed');

        $this->finishPasswordRecovery->finishPasswordRecovery($request);
    }
}
