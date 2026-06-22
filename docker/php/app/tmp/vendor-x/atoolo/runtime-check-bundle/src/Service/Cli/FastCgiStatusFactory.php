<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Cli;

use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ConfiguresSocketConnection;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;
use RuntimeException;

class FastCgiStatusFactory
{
    private readonly string $resourceRoot;
    private readonly string $resourceHost;

    /**
     * @param array<string> $possibleSocketFilePatterns
     * of patterns matching php socket files
     */
    public function __construct(
        private readonly array $possibleSocketFilePatterns,
        private readonly string $frontControllerPath,
        ?string $resourceRoot,
        ?string $resourceHost,
    ) {
        if (empty($resourceRoot)) {
            throw new RuntimeException(
                <<<EOF
                The resource-root could not be determined.
                This is the case if the console command was not
                called via the resource-root path
                EOF,
            );
        }
        if (empty($resourceHost)) {
            throw new RuntimeException(
                'resource host not set',
            );
        }

        $this->resourceRoot = $resourceRoot;
        $this->resourceHost = $resourceHost;
    }

    public function create(?string $socket = null): FastCgiStatus
    {
        $socket = $socket ?? $this->determineSocket();
        $client = new Client();
        $connection = $this->createConnection($socket);
        return new FastCgiStatus(
            $socket,
            $client,
            $connection,
            $this->frontControllerPath,
            $this->resourceRoot,
            $this->resourceHost,
        );
    }

    private function determineSocket(): string
    {
        foreach (
            $this->possibleSocketFilePatterns as $possibleSocketFilePattern
        ) {
            $files = glob($possibleSocketFilePattern) ?: [];
            $possibleSocketFile = current($files);
            if (
                $possibleSocketFile !== false
                && file_exists($possibleSocketFile)
            ) {
                return $possibleSocketFile;
            }
        }
        return '127.0.0.1:9000';
    }

    private function createConnection(
        string $socket,
    ): ConfiguresSocketConnection {
        $last = strrpos($socket, ':');
        if ($last !== false) {
            $port = substr($socket, $last + 1, strlen($socket));
            $host = substr($socket, 0, $last);

            return new NetworkSocket(
                $host,
                (int) $port,
                5000,     # Connect timeout in milliseconds (default: 5000)
                120000,    # Read/write timeout in milliseconds (default: 5000)
            );
        }

        return new UnixDomainSocket(
            $socket,
            5000,   # Connect timeout in milliseconds (default: 5000)
            120000,  # Read/write timeout in milliseconds (default: 5000)
        );
    }
}
