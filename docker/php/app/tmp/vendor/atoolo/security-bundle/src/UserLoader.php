<?php

declare(strict_types=1);

namespace Atoolo\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserLoader
{
    /**
     * @return array<string,UserInterface>
     */
    public function load(): array;
}
