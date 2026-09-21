<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test\Form;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MailPitClient
{
    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::createForBaseUri(e2eEnv('MAILPIT_ENDPOINT_BASE'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMessages(): array
    {
        $response = $this->client->request(
            'GET',
            '/api/v1/messages',
        );

        $json = json_decode(
            $response->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        /** @var array{messages: array<int, array<string, mixed>>} $json */
        return $json['messages'];
    }

    public function deleteAll(): void
    {
        $this->client->request(
            'DELETE',
            '/api/v1/messages',
        );
    }
}
