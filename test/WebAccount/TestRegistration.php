<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test\WebAccount;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TestRegistration extends TestCase
{
    private static string $ENDPOINT_BASE;

    public static function setUpBeforeClass(): void
    {
        // from phpunit.xml
        self::$ENDPOINT_BASE = $_SERVER['ENDPOINT_BASE'];
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testStartRegistration(): void
    {
        $client = HttpClient::create();

        $query = <<<'GRAPHQL'
mutation webAccountStartRegistration($configName: String!, $emailAddress: String!, $lang: String!) {
  webAccountStartRegistration(input: {
    configName: $configName
    emailAddress: $emailAddress
    lang: $lang
  }) {
    challengeId
    createAt
    expiresAt
  }
}
GRAPHQL;

        $variables = [
            'configName' => 'intranet',
            'emailAddress' => 'peter.pan@neverland.com',
            'lang' => 'en',
        ];

        $payload = ['query' => $query, 'variables' => $variables];

        $response = $client->request(
            'POST',
            self::$ENDPOINT_BASE . '/api/graphql/',
            [
                'json' => $payload,
            ],
        );

        $data = $response->toArray();

        if (isset($data['errors'])) {
            $this->fail(
                'Authentication failed: ' . $data['errors'][0]['message'] .
                "\nDebug message: " . $data['errors'][0]['extensions']['debugMessage'],
            );
        }

        if ($response->getStatusCode() !== 200) {
            $this->fail("Protected request should be successful:\n"
                . "Status code: " . $response->getStatusCode() . "\n"
                . "Response: " . $response->getContent(false));
        }

        $expected = [
            'data' => [
                'webAccountStartRegistration' => [
                    'challengeId' => 'cyEwLnyGtWZ2g2uHpItk8Mk8UaazZxCD',
                    'expiresAt' => '2025-12-05T12:35:14+00:00',
                    'createAt' => '2025-12-05T12:20:14+00:00',
                ],
            ],
        ];

        $this->assertEquals($expected, $data, 'Response data should match expected data');
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testFinishPasswordRecovery(): void
    {
        $client = HttpClient::create();

        $query = <<<'GRAPHQL'
mutation webAccountFinishPasswordRecovery {
  webAccountFinishPasswordRecovery(input: {
    configName: "intranet"
    lang: "de"
    challengeId: "1WPQbpNSMvpJb9L0jdcua7HUhQCJOqjg"
    code: 447343
    newPassword: "develop"
  })
}
GRAPHQL;

        $payload = ['query' => $query ];

        $response = $client->request(
            'POST',
            self::$ENDPOINT_BASE . '/api/graphql/',
            [
                'json' => $payload,
            ],
        );

        $data = $response->toArray();

        if (isset($data['errors'])) {
            $this->fail(
                'Authentication failed: ' . $data['errors'][0]['message'] .
                "\nDebug message: " . $data['errors'][0]['extensions']['debugMessage'],
            );
        }

        if ($response->getStatusCode() !== 200) {
            $this->fail("Protected request should be successful:\n"
                . "Status code: " . $response->getStatusCode() . "\n"
                . "Response: " . $response->getContent(false));
        }

        $expected = [
            'data' => [
                'webAccountFinishPasswordRecovery' => true,
            ],
        ];

        $this->assertEquals($expected, $data, 'Response data should match expected data');
    }
}
