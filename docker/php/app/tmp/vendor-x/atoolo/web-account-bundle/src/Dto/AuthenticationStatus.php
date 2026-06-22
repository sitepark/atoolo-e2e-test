<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

/**
 * @codeCoverageIgnore
 */
enum AuthenticationStatus: string
{
    case SUCCESS = 'SUCCESS';
    case PARTIAL = 'PARTIAL';
    case FAILURE = 'FAILURE';
}
