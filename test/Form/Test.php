<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test\Form;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    private HttpClientInterface $client;

    private MailPitClient $mailPitClient;

    public function setUp(): void
    {
        $this->client = HttpClient::createForBaseUri($_SERVER['ENDPOINT_BASE']);
        $this->mailPitClient = new MailPitClient();
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetFormDefinition(): void
    {

        $response = $this->client->request(
            'GET',
            '/api/form/de/forms/jsonForms/formEditor-1',
        );

        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedJson = json_decode(
            file_get_contents(__DIR__ . '/resources/definition.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertEquals(
            $expectedJson,
            $json,
            'Form definition does not match',
        );
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testSubmitForm(): void
    {
        $this->mailPitClient->deleteAll();

        $this->client->request(
            'POST',
            '/api/form/de/forms/jsonForms/formEditor-1',
            [
                'json' => [
                    "field-1" => "Text",
                ],
            ],
        );

        $messages = $this->mailPitClient->getMessages();
        $this->assertEquals('Atoolo form test', $messages[0]['Subject']);
    }
}
