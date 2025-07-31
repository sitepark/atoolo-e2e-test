<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test\WebAccount;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Test extends TestCase
{
    private static string $ENDPOINT_BASE;

    public static function setUpBeforeClass(): void
    {
        // from phpunit.xml
        self::$ENDPOINT_BASE = $_SERVER['ENDPOINT_BASE'];
    }

    public function testPublicRequest(): void
    {
        $client = HttpClient::create();

        $response = $client->request(
            'GET',
            self::$ENDPOINT_BASE . '/public-content',
        );

        $this->assertEquals(200, $response->getStatusCode(), 'Public request failed');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testProtectedUnauthorizedRequest(): void
    {
        $client = HttpClient::create();

        $response = $client->request(
            'GET',
            self::$ENDPOINT_BASE . '/protected-content',
            [
                'max_redirects' => 0,
            ],
        );

        if ($response->getStatusCode() !== 302) {
            $this->fail("Protected request should redirect to the login page without authentication:\n"
                . "Status code: " . $response->getStatusCode() . "\n"
                . "Response: " . $response->getContent(false));
        }

        # Header must be determined via getInfo(), otherwise a RedirectException will be thrown
        $location = $response->getInfo('redirect_url');

        $this->assertEquals(
            self::$ENDPOINT_BASE . '/account',
            $location,
            'Protected request should redirect to the login page',
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testProtectedAuthorizedRequest(): void
    {
        $client = HttpClient::create();

        $query = <<<'GRAPHQL'
mutation webaccountAuthenticationWithPassword($username: String!, $password: String!) {
  webaccountAuthenticationWithPassword(
    username: $username
    password: $password
    setJwtCookie: true
  ) {
    status
    user {
      id
      username
      firstName
      lastName
      email
      roles
    }
  }
}
GRAPHQL;

        $variables = [
            'username' => 'peterpan',
            'password' => 'tinkabell',
        ];

        $payload = ['query' => $query, 'variables' => $variables];

        $authResponse = $client->request(
            'POST',
            self::$ENDPOINT_BASE . '/api/graphql/',
            [
                'json' => $payload,
            ],
        );

        $authResponseData = $authResponse->toArray();

        if (isset($authResponseData['errors'])) {
            $this->fail(
                'Authentication failed: ' . $authResponseData['errors'][0]['message'] .
                "\nDebug message: " . $authResponseData['errors'][0]['extensions']['debugMessage'],
            );
        }

        $setCookieHeader = $authResponse->getHeaders()['set-cookie'] ??= [];

        $response = $client->request(
            'GET',
            self::$ENDPOINT_BASE . '/protected-content',
            [
                'headers' => [
                    'Cookie' => $setCookieHeader,
                ],
            ],
        );
        if ($response->getStatusCode() !== 200) {
            $this->fail("Protected request should be successful:\n"
                . "Status code: " . $response->getStatusCode() . "\n"
                . "Response: " . $response->getContent(false));
        }

        $this->assertEquals(200, $response->getStatusCode(), 'Protected request should be successful');
    }

}
