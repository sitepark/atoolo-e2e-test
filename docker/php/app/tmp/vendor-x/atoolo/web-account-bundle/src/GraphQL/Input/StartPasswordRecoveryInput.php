<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\GraphQL\Input;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @codeCoverageIgnore
 */
#[GQL\Input]
class StartPasswordRecoveryInput
{
    #[GQL\Field(type: "String!")]
    public string $configName;

    #[GQL\Field(type: "String!")]
    public string $username;

    #[GQL\Field(type: "String!")]
    public string $lang;
}
