<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test;

use JsonException;
use RuntimeException;

class TokenGenerator
{
    private static string $LOGIN_PATH = '/api/login_check';
    private ?string $token = null;
    private ?int $tokenCreatedAt = null;
    private int $tokenTtlInSeconds = 60;
    public function __construct(
        private readonly string $endpointBase,
    ) {}
    private static ?self $instance = null;
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self($_SERVER['ENTPOINT_BASE']);
        }

        return self::$instance;
    }

    public function getToken(): string
    {
        if ($this->token === null ||
            $this->tokenCreatedAt === null ||
            time() - $this->tokenCreatedAt > $this->tokenTtlInSeconds
        ) {
            $this->token = $this->fetchToken();
            $this->tokenCreatedAt = time();
        }

        return $this->token;
    }

    /**
     * @throws JsonException
     */
    private function fetchToken(): string
    {
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode([
                    'username' => 'api',
                    'password' => 'develop',
                ], JSON_THROW_ON_ERROR),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($this->endpointBase . self::$LOGIN_PATH, false, $context);
        if ($result === false) {
            throw new RuntimeException((error_get_last())['message'] ?? 'unknown error');
        }

        $statusLine = $http_response_header[0];
        preg_match('{HTTP/\S*\s(\d{3})}', $statusLine, $match);
        $status = (int) $match[1];

        if ($status !== 200) {
            throw new RuntimeException("HTTP request failed: $statusLine");
        }

        /** @var array{token:string} $data */
        $data = json_decode(
            $result,
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        /** @var string $token */
        $token = $data['token'];

        return $token;
    }
}
