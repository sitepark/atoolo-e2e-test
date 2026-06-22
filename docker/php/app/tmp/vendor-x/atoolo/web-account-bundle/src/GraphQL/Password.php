<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\GraphQL;

use Atoolo\WebAccount\Dto\FinishPasswordRecoveryRequest;
use Atoolo\WebAccount\Dto\StartPasswordRecoveryRequest;
use Atoolo\WebAccount\Dto\StartPasswordRecoveryResult;
use Atoolo\WebAccount\GraphQL\Input\FinishPasswordRecoveryInput;
use Atoolo\WebAccount\GraphQL\Input\StartPasswordRecoveryInput;
use Atoolo\WebAccount\Service\Password\FinishPasswordRecovery;
use Atoolo\WebAccount\Service\Password\StartPasswordRecovery;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[GQL\Provider]
class Password
{
    public function __construct(
        private readonly StartPasswordRecovery $startPasswordRecovery,
        private readonly FinishPasswordRecovery $finishPasswordRecovery,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    #[GQL\Mutation(name: 'webAccountStartPasswordRecovery', type: 'StartPasswordRecoveryResult!')]
    public function startPasswordRecovery(StartPasswordRecoveryInput $input): StartPasswordRecoveryResult
    {
        return $this->startPasswordRecovery->startPasswordRecovery(
            new StartPasswordRecoveryRequest(
                configName: $input->configName,
                lang: $input->lang,
                username: $input->username,
            ),
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[GQL\Mutation(name: 'webAccountFinishPasswordRecovery', type: 'Boolean!')]
    public function finishPasswordRecovery(FinishPasswordRecoveryInput $input): bool
    {
        $this->finishPasswordRecovery->finishPasswordRecovery(
            new FinishPasswordRecoveryRequest(
                configName: $input->configName,
                lang: $input->lang,
                challengeId: $input->challengeId,
                code: $input->code,
                newPassword: $input->newPassword,
            ),
        );
        return true;
    }
}
