<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service\Password;

use Atoolo\WebAccount\Dto\StartPasswordRecoveryRequest;
use Atoolo\WebAccount\Dto\StartPasswordRecoveryResult;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\ServiceException;
use DateTime;
use Exception;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class StartPasswordRecovery
{
    public function __construct(
        private readonly GraphQLClient $graphQLClient,
        private readonly ConfigurationLoader $configurationLoader,
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function startPasswordRecovery(StartPasswordRecoveryRequest $request): StartPasswordRecoveryResult
    {

        $config = $this->configurationLoader->load($request->configName);

        $query = <<<'GRAPHQL'
mutation startPasswordRecovery($input: StartPasswordRecoveryInput!) {
  security {
    password {
      startPasswordRecovery(input: $input) {
        challengeId
        createdAt
        expiresAt
      }
    }
  }
}
GRAPHQL;

        $variables = [
            'input' => [
                'username' => $request->username,
                'emailParameters' => [
                    'from' => [
                        'address' => $config->email->from->getAddress(),
                        'name' => $config->email->from->getName(),
                    ],
                    'replyTo' => [
                        [
                            'address' => $config->email->replyTo->getAddress(),
                            'name' => $config->email->replyTo->getName(),
                        ],
                    ],
                    'lang' => $request->lang,
                    'theme' => $config->email->theme,
                ],
            ],
        ];

        /**
         * @param array{data: array{security: array{password: array{startPasswordRecovery: array{challengeId: string, createdAt: string, expiresAt: string}}}}} $data
         */
        $responseMapper = static function (array $data) {
            $result = $data['data']['security']['password']['startPasswordRecovery'];
            return new StartPasswordRecoveryResult(
                challengeId: $result['challengeId'],
                createAt: new DateTime($result['createdAt']),
                expiresAt: new DateTime($result['expiresAt']),
            );
        };

        return $this->graphQLClient->request(
            $query,
            $variables,
            $config->apiKey,
            $responseMapper,
            static function (string $errorClass, string $errorMessage, array $errorData): ?\Exception {
                if ($errorClass === 'StartPasswordRecoveryException') {
                    return new ServiceException('Start password recovery failed: ' . $errorMessage);
                }
                return null;
            },
        );
    }
}
