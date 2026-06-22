<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\GraphQL\Input;

use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @codeCoverageIgnore
 */
#[GQL\Input]
class UserProfileInput
{
    #[GQL\Field(type: "String")]
    public ?string $firstName = null;

    #[GQL\Field(type: "String")]
    public ?string $lastName = null;

}
