<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service;

use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\IesUrlResolver;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(GraphQLClient::class)]
class GraphQLClientTest extends TestCase
{
    private readonly IesUrlResolver&MockObject $iesUrlResolver;

    private readonly HttpClientInterface&MockObject $httpClient;

    private readonly ResponseInterface&MockObject $httpResponse;

    private readonly GraphQLClient $graphQLClient;

    protected function setUp(): void
    {
        $this->iesUrlResolver = $this->createMock(IesUrlResolver::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpResponse = $this->createMock(ResponseInterface::class);

        $this->iesUrlResolver
            ->method('getBaseUrl')
            ->willReturn('https://ies.example.com');

        $this->graphQLClient = new GraphQLClient(
            $this->iesUrlResolver,
            $this->httpClient,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestWithSuccessfulResponse(): void
    {
        $query = 'query { test }';
        $variables = ['var' => 'value'];
        $userToken = 'test-token';

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://ies.example.com/api/graphql',
                $this->callback(function ($options) use ($query, $variables, $userToken) {
                    return isset($options['json']) &&
                        $options['json']['query'] === $query &&
                        $options['json']['variables'] === $variables &&
                        isset($options['headers']['IES-USER-TOKEN']) &&
                        $options['headers']['IES-USER-TOKEN'] === $userToken;
                }),
            )
            ->willReturn($this->httpResponse);

        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willReturn([
            'data' => ['result' => 'success'],
        ]);

        $responseMapper = static fn(array $data) => $data['data']['result'];
        $errorMapper = static fn(string $class, string $msg, array $data): ?\Exception => null;

        $result = $this->graphQLClient->request(
            $query,
            $variables,
            $userToken,
            $responseMapper,
            $errorMapper,
        );

        $this->assertEquals('success', $result, "The response mapper should return the correct result");
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestWithoutUserToken(): void
    {
        $query = 'query { test }';
        $variables = [];

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://ies.example.com/api/graphql',
                $this->callback(function ($options) {
                    return !isset($options['headers']['IES-USER-TOKEN']);
                }),
            )
            ->willReturn($this->httpResponse);

        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willReturn(['data' => []]);

        $responseMapper = static fn(array $data) => true;
        $errorMapper = static fn(string $class, string $msg, array $data): ?\Exception => null;

        $this->graphQLClient->request(
            $query,
            $variables,
            null,
            $responseMapper,
            $errorMapper,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestThrowsRuntimeExceptionOnNon200StatusCode(): void
    {
        $this->httpClient->method('request')->willReturn($this->httpResponse);
        $this->httpResponse->method('getStatusCode')->willReturn(500);
        $this->httpResponse->method('getContent')->willReturn('Internal Server Error');

        $responseMapper = static fn(array $data) => $data;
        $errorMapper = static fn(string $class, string $msg, array $data): ?\Exception => null;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP Status Code from https://ies.example.com/api/graphql: 500');

        $this->graphQLClient->request(
            'query { test }',
            [],
            null,
            $responseMapper,
            $errorMapper,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestThrowsRuntimeExceptionOnInvalidJson(): void
    {
        $this->httpClient->method('request')->willReturn($this->httpResponse);
        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willThrowException(new Exception('Invalid JSON'));
        $this->httpResponse->method('getContent')->willReturn('Invalid response');

        $responseMapper = static fn(array $data) => $data;
        $errorMapper = static fn(string $class, string $msg, array $data): ?\Exception => null;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Response from https://ies.example.com could not be decoded');

        $this->graphQLClient->request(
            'query { test }',
            [],
            null,
            $responseMapper,
            $errorMapper,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestHandlesMappedError(): void
    {
        $this->httpClient->method('request')->willReturn($this->httpResponse);
        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willReturn([
            'errors' => [
                [
                    'message' => 'Custom error',
                    'extensions' => [
                        'exception' => [
                            'className' => 'com.example.CustomException',
                        ],
                    ],
                ],
            ],
        ]);

        $responseMapper = static fn(array $data) => $data;
        $errorMapper = static fn(string $class, string $msg, array $data): ?\Exception => $class === 'CustomException' ? new Exception('Mapped error') : null;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mapped error');

        $this->graphQLClient->request(
            'query { test }',
            [],
            null,
            $responseMapper,
            $errorMapper,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestThrowsRuntimeExceptionOnUnmappedError(): void
    {
        $this->httpClient->method('request')->willReturn($this->httpResponse);
        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willReturn([
            'errors' => [
                [
                    'message' => 'Unmapped error',
                    'extensions' => [
                        'exception' => [
                            'className' => 'com.example.UnknownException',
                        ],
                    ],
                ],
            ],
        ]);

        $responseMapper = static fn(array $data) => $data;
        $errorMapper = static fn(string $class, string $msg, array $data): ?\Exception => null;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unmapped error');

        $this->graphQLClient->request(
            'query { test }',
            [],
            null,
            $responseMapper,
            $errorMapper,
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestThrowsRuntimeExceptionOnErrorWithoutException(): void
    {
        $this->httpClient->method('request')->willReturn($this->httpResponse);
        $this->httpResponse->method('getStatusCode')->willReturn(200);
        $this->httpResponse->method('toArray')->willReturn([
            'errors' => [
                [
                    'message' => 'Generic error',
                ],
            ],
        ]);

        $responseMapper = static fn(array $data) => $data;
        $errorMapper = static fn(string $class, string $msg, array $data): ?\Exception => null;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Generic error');

        $this->graphQLClient->request(
            'query { test }',
            [],
            null,
            $responseMapper,
            $errorMapper,
        );
    }
}
