<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service\Registration;

use Atoolo\WebAccount\Dto\StartRegistrationRequest;
use Atoolo\WebAccount\Dto\StartRegistrationResult;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\ServiceException;
use DateTime;
use Exception;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class StartRegistration
{
    public function __construct(
        private readonly GraphQLClient $graphQLClient,
        private readonly ConfigurationLoader $configurationLoader,
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws ServiceException
     * @throws Exception
     */
    public function startRegistration(StartRegistrationRequest $request): StartRegistrationResult
    {

        $config = $this->configurationLoader->load($request->configName);

        $query = <<<'GRAPHQL'
mutation startUserRegistration($input: StartUserRegistrationInput!) {
    user {
        startUserRegistration(input: $input) {
            challengeId
            createdAt
            expiresAt
        }
    }
}
GRAPHQL;

        $variables = [
            'input' => [
                'email' => $request->emailAddress,
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
         * @param array{data: array{user: array{startUserRegistration: array{challengeId: string, createdAt: string, expiresAt: string}}}} $data
         */
        $responseMapper = static function (array $data) {
            $result = $data['data']['user']['startUserRegistration'];
            return new StartRegistrationResult(
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
                if ($errorClass === 'StartUserRegistrationException') {
                    return new ServiceException('Start user registration failed: ' . $errorMessage);
                }
                return null;
            },
        );
    }
}
