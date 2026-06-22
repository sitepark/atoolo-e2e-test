<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\GraphQL\Input;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @codeCoverageIgnore
 */
#[GQL\Input]
class StartRegistrationInput
{
    #[GQL\Field(type: "String!")]
    public string $configName;

    #[GQL\Field(type: "String!")]
    public string $emailAddress;

    #[GQL\Field(type: "String!")]
    public string $lang;
}
