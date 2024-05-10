<?php

declare(strict_types=1);

namespace Atoolo\Test\GraphQL;

use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Softonic\GraphQL\ClientBuilder;
use Symfony\Component\Finder\Finder;

class Test extends TestCase
{
    private static string $ENDPOINT_BASE = 'http://atoolo-e2e-test:9090';

    private static string $GRAPHQL_PATH = '/api/graphql/';

    private static string $LOGIN_PATH = '/api/login_check';

    private ?string $token = null;

    private ?int $tokenCreatedAt = null;

    private int $tokenTtlInSeconds = 60;

    /**
     * @throws JsonException
     */
    public static function graphqlQuery(): array
    {
        $finder = new Finder();
        $finder->files()
            ->in(__DIR__ . '/resources')
            ->name('*.graphql')
            ->sortByName();

        $data = [];

        foreach ($finder as $file) {
            $query = $file->getContents();

            $tokenRequired = str_contains($query, '@test:token-required');

            $resultJsonFileName = $file->getBasename('.graphql') .
                '.result.json';
            $resultJsonFile = $file->getPath() . '/' . $resultJsonFileName;

            if (!file_exists($resultJsonFile)) {
                throw new RuntimeException(
                    'Missing result file ' . $resultJsonFile
                );
            }
            $resultJson = json_decode(
                file_get_contents($resultJsonFile),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $data[$file->getRelativePathname()] = [
                $file->getPathname(),
                $resultJsonFile,
                $query,
                $resultJson,
                $tokenRequired
            ];
        }

        return $data;
    }

    /**
     * @throws JsonException
     */
    #[DataProvider('graphqlQuery')]
    public function test(
        string $queryFile,
        string $resultFile,
        string $query,
        array $resultJson,
        bool $tokenRequired
    ): void {

        $headers = [];

        if ($tokenRequired) {
            $headers['Authorization'] = 'Bearer ' . $this->getToken();
        }

        $client = ClientBuilder::build(
            self::$ENDPOINT_BASE . self::$GRAPHQL_PATH,
            [
                'headers' => $headers
            ]
        );
        $response = $client->query($query);

        if ($response->hasErrors()) {
            $messages = array_map(
                static fn($error) =>
                    $error['message'] .
                    "\npath: " . implode('/', $error['path']),
                $response->getErrors()
            );
            $this->fail(
                $queryFile . "\n" .
                $resultFile . "\n" .
                "Errors:\n" .
                implode("\n", $messages)
            );
        } else {
            $this->assertEquals(
                $resultJson,
                $response->getData(),
                $queryFile . "\n" . $resultFile
            );
        }
    }

    /**
     * @throws JsonException
     */
    private function getToken(): string
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
                    'password' => 'develop'
                ], JSON_THROW_ON_ERROR),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents(self::$ENDPOINT_BASE . self::$LOGIN_PATH, false, $context);

        $statusLine = $http_response_header[0];
        preg_match('{HTTP/\S*\s(\d{3})}', $statusLine, $match);
        $status = (int)$match[1];

        if ($status !== 200) {
            throw new RuntimeException("HTTP request failed: $statusLine");
        }

        $data = json_decode(
            $result,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        /** @var string $token */
        $token = $data['token'];

        return $token;
    }
}
