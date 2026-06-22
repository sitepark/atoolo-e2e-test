<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Cli;

use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ConfiguresSocketConnection;
use hollodotme\FastCGI\Requests\PostRequest;
use JsonException;
use Throwable;

class FastCgiStatus
{
    /**
     * @param string $socket 127.0.0.1:9000 or /var/run/php8.3-fpm.sock
     */
    public function __construct(
        private readonly string $socket,
        private readonly Client $client,
        private readonly ConfiguresSocketConnection $connection,
        private readonly string $frontControllerPath,
        private readonly string $resourceRoot,
        private readonly string $resourceHost,
    ) {}

    /**
     * @internal For testing purposes only
     * @codeCoverageIgnore
     */
    public function getSocket(): string
    {
        return $this->socket;
    }

    /**
     * @internal For testing purposes only
     * @codeCoverageIgnore
     */
    public function getConnection(): ConfiguresSocketConnection
    {
        return $this->connection;
    }

    /**
     * @param array<string> $skip
     */
    public function request(array $skip): CheckStatus
    {
        foreach (RuntimeType::otherCases(RuntimeType::FPM_FCGI) as $type) {
            $skip[] = $type->value;
        }
        $content = http_build_query(['skip' => $skip]);

        $request = new PostRequest($this->frontControllerPath, $content);
        $request->setRequestUri('/api/runtime-check');
        $request->setRemoteAddress('127.0.0.1');
        $request->setServerName($this->resourceHost);
        $request->setCustomVar('RESOURCE_ROOT', $this->resourceRoot);
        $request->setCustomVar(
            'DOCUMENT_ROOT',
            dirname($this->frontControllerPath),
        );

        try {
            $res =  $this->client->sendRequest(
                $this->connection,
                $request,
            );

            if (!empty($res->getError())) {
                return CheckStatus::createFailure()
                    ->addMessage(
                        'fpm-fcgi',
                        sprintf(
                            "FPM Error output:\n%s",
                            $res->getError(),
                        ),
                    );
            }

            $body = $res->getBody();
            try {
                /** @var RuntimeStatusData $data */
                $data = json_decode(
                    $body,
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                );
            } catch (JsonException $e) {
                return CheckStatus::createFailure()
                    ->addMessage(
                        'fpm-fcgi',
                        sprintf(
                            "JSON error: %s\n%s",
                            $e->getMessage(),
                            $body,
                        ),
                    );
            }

            $status = RuntimeStatus::deserialize($data)
                ->getStatus(RuntimeType::FPM_FCGI);
            return $status ?? CheckStatus::createFailure()
                ->addMessage(
                    'fpm-fcgi',
                    'No FastCGI status found in response.',
                );
        } catch (Throwable $e) {
            return CheckStatus::createFailure()
                ->addMessage(
                    'fpm-fcgi',
                    sprintf(
                        'FastCGI error: %s (%s)',
                        $e->getMessage(),
                        $this->socket,
                    ),
                );
        }
    }
}
