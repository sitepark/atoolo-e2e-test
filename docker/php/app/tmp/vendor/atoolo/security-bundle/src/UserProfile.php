<?php

declare(strict_types=1);

namespace Atoolo\Security;

interface UserProfile
{
    public function getFirstName(): ?string;

    public function getLastName(): ?string;

    public function getEmail(): ?string;
}
