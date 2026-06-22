<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\GraphQL\Input;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @codeCoverageIgnore
 */
#[GQL\Input]
class FinishRegistrationInput
{
    #[GQL\Field(type: "String!")]
    public string $configName;

    #[GQL\Field(type: "String!")]
    public string $lang;

    #[GQL\Field(type: "String!")]
    public string $challengeId;

    #[GQL\Field(type: "Int!")]
    public int $code;

    #[GQL\Field(type: "String")]
    public ?string $firstName = null;

    #[GQL\Field(type: "String!")]
    public string $lastName;

    #[GQL\Field(type: "String!")]
    public string $password;
}
