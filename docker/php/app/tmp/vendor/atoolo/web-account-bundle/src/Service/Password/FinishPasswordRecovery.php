<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service\Password;

use Atoolo\WebAccount\Dto\FinishPasswordRecoveryRequest;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use Atoolo\WebAccount\Service\GraphQLClient;
use Atoolo\WebAccount\Service\ServiceException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FinishPasswordRecovery
{
    public function __construct(
        private readonly GraphQLClient $graphQLClient,
        private readonly ConfigurationLoader $configurationLoader,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function finishPasswordRecovery(FinishPasswordRecoveryRequest $request): void
    {

        $config = $this->configurationLoader->load($request->configName);

        $query = <<<'GRAPHQL'
mutation finishPasswordRecovery($input: FinishPasswordRecoveryInput!) {
  security {
    password {
      finishPasswordRecovery(input: $input)
    }
  }
}
GRAPHQL;

        $variables = [
            'input' => [
                'challengeId' => $request->challengeId,
                'code' => $request->code,
                'newPassword' => $request->newPassword,
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
         * @param array{data?: array{security?: array{password?: array{finishPasswordRecovery?: mixed}}}} $data
         */
        $successChecker = static fn(array $data) => isset($data['data']['security']['password']['finishPasswordRecovery']);

        $this->graphQLClient->request(
            $query,
            $variables,
            $config->apiKey,
            $successChecker,
            static function (string $errorClass, string $errorMessage, array $errorData) {
                if ($errorClass === 'CodeVerificationFailedException') {
                    return new ServiceException('Code verification failed');
                }
                if ($errorClass === 'FinishPasswordRecoveryException') {
                    return new ServiceException('Finish password recovery failed');
                }
                return null;
            },
        );
    }
}
