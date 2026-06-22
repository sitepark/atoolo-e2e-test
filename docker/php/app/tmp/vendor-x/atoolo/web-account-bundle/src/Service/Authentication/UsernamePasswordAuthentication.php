<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service\Authentication;

use Atoolo\WebAccount\Dto\AuthenticationResult;
use Atoolo\WebAccount\Service\IesUrlResolver;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class UsernamePasswordAuthentication
{
    public function __construct(
        private readonly IesUrlResolver $iesUrlResolver,
        private readonly HttpClientInterface $client,
        private readonly DenormalizerInterface $denormalizer,
    ) {}

    /**
     * @throws \JsonException
     * @throws TransportExceptionInterface
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function authenticate(string $username, string $password): AuthenticationResult
    {

        $query = <<<'GRAPHQL'
mutation authenticate($username: String!, $password: String!) {
  security {
    authenticate {
      withPassword(
        username: $username
        password: $password
        purpose: "atoolo_web_account"
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
  }
}
GRAPHQL;

        $variables = [
            'username' => $username,
            'password' => $password,
        ];

        $payload = ['query' => $query, 'variables' => $variables];

        $url = $this->iesUrlResolver->getBaseUrl() . '/api/graphql';

        $response = $this->client->request(
            'POST',
            $url,
            [
                'json' => $payload,
            ],
        );

        if (200 !== $response->getStatusCode()) {
            $content = null;
            try {
                $content = $response->getContent();
            } catch (Throwable $e) { // @codeCoverageIgnore
            }
            throw new RuntimeException(
                'HTTP Status Code from '
                . $url
                . ': '
                . $response->getStatusCode()
                . "\nMessage:\n"
                . $content,
            );
        }

        try {
            $responseData = $response->toArray();
        } catch (Throwable $e) {
            $content = null;
            try {
                $content = $response->getContent();
            } catch (Throwable $e) { // @codeCoverageIgnore
            }
            throw new RuntimeException(
                'Response from '
                . $this->iesUrlResolver->getBaseUrl()
                . ' could not be decoded: '
                . $e->getMessage()
                . "\nMessage:\n"
                . $content,
            );
        }

        if (!empty($responseData['errors'] ?? [])) {
            throw new RuntimeException($responseData['errors'][0]['message'] ?? 'Login failed');
        }

        $data = $responseData['data']['security']['authenticate']['withPassword'] ?? [];

        /** @var AuthenticationResult $result */
        $result = $this->denormalizer->denormalize($data, AuthenticationResult::class);
        return $result;

    }
}
