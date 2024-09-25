<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test\GraphQL;

use Atoolo\E2E\Test\TokenGenerator;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Softonic\GraphQL\ClientBuilder;
use Symfony\Component\Finder\Finder;

class Test extends TestCase
{
    private static string $ENDPOINT_BASE;

    private static string $GRAPHQL_PATH = '/api/graphql/';

    public static function setUpBeforeClass(): void
    {
        // from phpunit.xml
        self::$ENDPOINT_BASE = $_SERVER['ENDPOINT_BASE'];
    }

    /**
     * @throws JsonException
     * @return array<string, array<int, mixed>>
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
                    'Missing result file ' . $resultJsonFile,
                );
            }
            $resultJson = json_decode(
                file_get_contents($resultJsonFile) ?: '{}',
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            $data[$file->getRelativePathname()] = [
                $file->getPathname(),
                $resultJsonFile,
                $query,
                $resultJson,
                $tokenRequired,
            ];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $resultJson
     * @throws JsonException
     */
    #[DataProvider('graphqlQuery')]
    public function test(
        string $queryFile,
        string $resultFile,
        string $query,
        array $resultJson,
        bool $tokenRequired,
    ): void {

        $headers = [];

        if ($tokenRequired) {
            $headers['Authorization'] = 'Bearer ' . $this->getToken();
        }

        $client = ClientBuilder::build(
            self::$ENDPOINT_BASE . self::$GRAPHQL_PATH,
            [
                'headers' => $headers,
            ],
        );
        $response = $client->query($query);

        if ($response->hasErrors()) {
            $messages = array_map(
                static fn($error) =>
                    $error['message'] .
                    "\npath: " . implode('/', $error['path']),
                $response->getErrors(),
            );
            $this->fail(
                $queryFile . "\n" .
                $resultFile . "\n" .
                "Errors:\n" .
                implode("\n", $messages),
            );
        } else {
            $this->assertEquals(
                $resultJson,
                $response->getData(),
                $queryFile . "\n" . $resultFile,
            );
        }
    }

    /**
     * @throws JsonException
     */
    private function getToken(): string
    {
        return TokenGenerator::getInstance()->getToken();
    }
}
