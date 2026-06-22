<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Cli;

use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Cli\FastCgiStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ConfiguresSocketConnection;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(FastCgiStatus::class)]
class FastCGIStatusTest extends TestCase
{
    private Client $client;
    private ConfiguresSocketConnection $connection;
    private FastCGIStatus $fastCGIStatus;
    private ProvidesResponseData $response;

    public function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->connection = $this->createMock(
            ConfiguresSocketConnection::class,
        );
        $this->response = $this->createStub(ProvidesResponseData::class);

        $this->fastCGIStatus = new FastCGIStatus(
            'mysocket',
            $this->client,
            $this->connection,
            '/path/to/front-controller',
            '',
            '',
        );
    }

    public function testRequest(): void
    {
        $this->response->method('getBody')
            ->willReturn(json_encode([
                'success' => true,
                RuntimeType::FPM_FCGI->value => [
                    'success' => true,
                    'reports' => [
                        'test' => [
                            'a' => 'b',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR));
        $this->client->method('sendRequest')
            ->willReturn($this->response);


        $status = $this->fastCGIStatus->request([]);

        $expected = CheckStatus::createSuccess();
        $expected->addReport('test', [
            'a' => 'b',
        ]);

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected CheckStatus',
        );
    }

    public function testRequestMissingFpmFcgi(): void
    {
        $this->response->method('getBody')
            ->willReturn(json_encode([
                'success' => true,
            ], JSON_THROW_ON_ERROR));
        $this->client->method('sendRequest')
            ->willReturn($this->response);

        $status = $this->fastCGIStatus->request([]);

        $expected = CheckStatus::createFailure();
        $expected->addMessage(
            'fpm-fcgi',
            'No FastCGI status found in response.',
        );

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected CheckStatus',
        );
    }

    public function testRequestWithFpmError(): void
    {
        $this->response->method('getError')
            ->willReturn('FPM Error');
        $this->client->method('sendRequest')
            ->willReturn($this->response);

        $status = $this->fastCGIStatus->request([]);

        $expected = CheckStatus::createFailure();
        $expected->addMessage(
            'fpm-fcgi',
            "FPM Error output:\nFPM Error",
        );

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected CheckStatus',
        );
    }

    public function testRequestWithInvalidJson(): void
    {
        $this->response->method('getBody')
            ->willReturn('invalid-json');
        $this->client->method('sendRequest')
            ->willReturn($this->response);

        $status = $this->fastCGIStatus->request([]);

        $expected = CheckStatus::createFailure();
        $expected->addMessage(
            'fpm-fcgi',
            "JSON error: Syntax error\ninvalid-json",
        );

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected CheckStatus',
        );
    }

    public function testRequestWithException(): void
    {
        $this->client->method('sendRequest')
            ->willThrowException(new RuntimeException('error'));

        $status = $this->fastCGIStatus->request([]);

        $expected = CheckStatus::createFailure();
        $expected->addMessage(
            'fpm-fcgi',
            'FastCGI error: error (mysocket)',
        );

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected CheckStatus',
        );
    }
}
