<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service\Authentication;

use Atoolo\WebAccount\Dto\AuthenticationResult;
use Atoolo\WebAccount\Dto\AuthenticationStatus;
use Atoolo\WebAccount\Dto\User;
use Atoolo\WebAccount\Service\Authentication\UsernamePasswordAuthentication;
use Atoolo\WebAccount\Service\IesUrlResolver;
use Exception;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(UsernamePasswordAuthentication::class)]
class UsernamePasswordAuthenticationTest extends TestCase
{
    private readonly IesUrlResolver $iesUrlResolver;
    private readonly HttpClientInterface $httpClient;
    private readonly DenormalizerInterface $denormalizer;
    private readonly ResponseInterface $httpResponse;
    private readonly UsernamePasswordAuthentication $authentication;

    protected function setUp(): void
    {
        $this->iesUrlResolver = $this->createMock(IesUrlResolver::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpResponse = $this->createMock(ResponseInterface::class);
        $this->httpClient->method('request')->willReturn($this->httpResponse);

        $this->denormalizer = $this->createMock(DenormalizerInterface::class);

        $this->authentication = new UsernamePasswordAuthentication(
            $this->iesUrlResolver,
            $this->httpClient,
            $this->denormalizer,
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    public function testAuthenticateWithNon200StatusCode(): void
    {
        $this->httpResponse->method('getStatusCode')->willReturn(500);
        $this->expectException(RuntimeException::class);
        $this->authentication->authenticate('testuser', 'testpassword');
    }

    /**
     * @throws ExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    public function testAuthenticateWithResponseNotDecodable(): void
    {
        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willThrowException(new Exception());
        $this->expectException(RuntimeException::class);
        $this->authentication->authenticate('testuser', 'testpassword');
    }

    /**
     * @throws ExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    public function testAuthenticateWithResponseErrorData(): void
    {
        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willReturn([
            'errors' => [
                [
                    'message' => 'Authentication failed',
                ],
            ],
        ]);
        $this->expectException(RuntimeException::class);
        $this->authentication->authenticate('testuser', 'testpassword');
    }

    /**
     * @throws ExceptionInterface
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    public function testAuthenticate(): void
    {
        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willReturn([
            'data' => [
                [
                    'security' => [
                        'authenticate' => [
                            'withPassword' => [
                                'status' => 'SUCCESS',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $expected = new AuthenticationResult(
            AuthenticationStatus::SUCCESS,
            new User(
                '1',
                'peterpan',
                'Peter',
                'Pan',
                'peterpan@neverland.com',
                ['A'],
            ),
        );

        $this->denormalizer->expects(self::once())->method('denormalize')->willReturn($expected);
        $result = $this->authentication->authenticate('testuser', 'testpassword');

        $this->assertSame($expected, $result, 'Authentication result does not match expected value');
    }

}
