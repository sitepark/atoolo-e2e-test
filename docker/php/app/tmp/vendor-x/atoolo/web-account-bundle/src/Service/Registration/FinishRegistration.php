<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service\Registration;

use Atoolo\WebAccount\Dto\FinishRegistrationRequest;
use Atoolo\WebAccount\Dto\FinishRegistrationResult;
use Atoolo\WebAccount\Exception\EmailAlreadyExistsException;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\ServiceException;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FinishRegistration
{
    public function __construct(
        private readonly GraphQLClient $graphQLClient,
        private readonly ConfigurationLoader $configurationLoader,
    ) {}

    /**
     * @throws ServiceException
     * @throws TransportExceptionInterface
     * @throws EmailAlreadyExistsException
     */
    public function finishRegistration(FinishRegistrationRequest $request): FinishRegistrationResult
    {
        $config = $this->configurationLoader->load($request->configName);

        $query = <<<'GRAPHQL'
mutation finishUserRegistration($input: FinishUserRegistrationInput!) {
    user {
        finishUserRegistration(input: $input) {
            __typename
            ... on FinishUserRegistrationResult {
                id
                email
            }
            ... on EmailAlreadyExistsError {
                email
            }
        }
    }
}
GRAPHQL;

        $variables = [
            'input' => [
                'challengeId' => $request->challengeId,
                'code' => $request->code,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'password' => $request->password,
                'roleIdentifiers' => $config->registration->roleIds,
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
         * @param array{data: array{user: array{finishUserRegistration: array{__typename: string, id?: string, email: string}}}} $data
         */
        $responseMapper = static function (array $data) {
            $result = $data['data']['user']['finishUserRegistration'];
            $type = $result['__typename'];
            switch ($type) {
                case 'FinishUserRegistrationResult':
                    return new FinishRegistrationResult($result['id'], $result['email']);
                case 'EmailAlreadyExistsError':
                    throw new EmailAlreadyExistsException($result['email']);
                default:
                    throw new RuntimeException('Unexpected response type: ' . $type);
            }
        };

        return $this->graphQLClient->request(
            $query,
            $variables,
            $config->apiKey,
            $responseMapper,
            static function (string $errorClass, string $errorMessage, array $errorData): ?\Exception {
                if ($errorClass === 'FinishUserRegistrationException') {
                    return new ServiceException('Finish user registration failed: ' . $errorMessage);
                }
                return null;
            },
        );
    }
}
