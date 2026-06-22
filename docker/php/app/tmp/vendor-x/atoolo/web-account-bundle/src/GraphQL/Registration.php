<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\GraphQL;

use Atoolo\WebAccount\Dto\EmailAlreadyExistsError;
use Atoolo\WebAccount\Dto\FinishRegistrationRequest;
use Atoolo\WebAccount\Dto\FinishRegistrationResult;
use Atoolo\WebAccount\Dto\StartRegistrationRequest;
use Atoolo\WebAccount\Dto\StartRegistrationResult;
use Atoolo\WebAccount\Exception\EmailAlreadyExistsException;
use Atoolo\WebAccount\GraphQL\Input\FinishRegistrationInput;
use Atoolo\WebAccount\GraphQL\Input\StartRegistrationInput;
use Atoolo\WebAccount\Service\Registration\FinishRegistration;
use Atoolo\WebAccount\Service\Registration\StartRegistration;
use Atoolo\WebAccount\Service\ServiceException;
use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Annotation as GQL;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Overblog\GraphQLBundle\Resolver\UnresolvableException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[GQL\Provider]
class Registration implements MutationInterface, AliasedInterface
{
    public function __construct(
        private readonly StartRegistration $startRegistration,
        private readonly FinishRegistration $finishRegistration,
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws ServiceException
     */
    #[GQL\Mutation(name: 'webAccountStartRegistration', type: 'StartRegistrationResult!')]
    public function startRegistration(StartRegistrationInput $input): StartRegistrationResult
    {
        return $this->startRegistration->startRegistration(new StartRegistrationRequest(
            configName: $input->configName,
            lang: $input->lang,
            emailAddress: $input->emailAddress,
        ));
    }

    /**
     * @throws ServiceException
     * @throws TransportExceptionInterface
     */
    #[GQL\Mutation(name: 'webAccountFinishRegistration', type: 'FinishUserRegistrationResultType!')]
    public function finishRegistration(
        FinishRegistrationInput $input,
    ): FinishRegistrationResult|EmailAlreadyExistsError {
        try {
            return $this->finishRegistration->finishRegistration(new FinishRegistrationRequest(
                $input->configName,
                $input->lang,
                $input->challengeId,
                $input->code,
                $input->firstName,
                $input->lastName,
                $input->password,
            ));
        } catch (EmailAlreadyExistsException $e) {
            return new EmailAlreadyExistsError(email: $e->email);
        }
    }

    public function mapResponseType(mixed $value, TypeResolver $typeResolver): ?Type
    {
        if ($value instanceof FinishRegistrationResult) {
            return $typeResolver->resolve('FinishRegistrationResult');
        }

        if ($value instanceof EmailAlreadyExistsError) {
            return $typeResolver->resolve('EmailAlreadyExistsError');
        }

        throw new UnresolvableException("Couldn't resolve type for union 'ResetPasswordResponse'");
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'mapResponseType' => 'map_response_type',
        ];
    }

}
