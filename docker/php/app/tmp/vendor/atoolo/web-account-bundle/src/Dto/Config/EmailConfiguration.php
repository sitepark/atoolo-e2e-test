<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto\Config;

use Symfony\Component\Mime\Address;

/**
 * @codeCoverageIgnore
 */
class EmailConfiguration
{
    public function __construct(
        public readonly string $theme,
        public readonly Address $from,
        public readonly Address $replyTo,
    ) {}

}
