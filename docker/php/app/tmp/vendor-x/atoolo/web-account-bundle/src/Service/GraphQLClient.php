<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service;

use Exception;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class GraphQLClient
{
    public function __construct(
        private readonly IesUrlResolver $iesUrlResolver,
        private readonly HttpClientInterface $client,
    ) {}

    /**
     * @template T
     * @param string $query
     * @param array<string,mixed> $variables
     * @param string|null $userToken
     * @param callable(array<string,mixed>): T $responseMapper
     * @param callable(string,string,array<string,mixed>): ?\Exception $errorMapper
     * @return T
     * @throws TransportExceptionInterface
     */
    public function request(
        string $query,
        array $variables,
        ?string $userToken,
        callable $responseMapper,
        callable $errorMapper,
    ): mixed {

        $payload = ['query' => $query, 'variables' => $variables];

        $url = $this->iesUrlResolver->getBaseUrl() . '/api/graphql';

        $headers = ['ABC' => 'CDE'];
        if ($userToken !== null) {
            $headers['IES-USER-TOKEN'] = $userToken;
        }

        $response = $this->client->request(
            'POST',
            $url,
            [
                'json' => $payload,
                'headers' => $headers,
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
            $error = $responseData['errors'][0];
            $errorMessage = $error['message'] ?? 'Internal Error';
            if (isset($error['extensions']['exception'])) {
                $errorClassName = explode('.', $error['extensions']['exception']['className']);
                $errorSimpleClassName = end($errorClassName);
                $exception = $errorMapper($errorSimpleClassName, $errorMessage, $responseData);
                if ($exception !== null) {
                    throw $exception;
                }
            }
            throw new RuntimeException($responseData['errors'][0]['message'] ?? 'Internal Error');
        }

        return $responseMapper($responseData);
    }
}
