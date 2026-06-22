<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\GraphQL\Input;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @codeCoverageIgnore
 */
#[GQL\Input]
class FinishPasswordRecoveryInput
{
    #[GQL\Field(type: "String!")]
    public string $configName;

    #[GQL\Field(type: "String!")]
    public string $lang;

    #[GQL\Field(type: "String!")]
    public string $challengeId;

    #[GQL\Field(type: "Int!")]
    public int $code;

    #[GQL\Field(type: "String!")]
    public string $newPassword;
}
