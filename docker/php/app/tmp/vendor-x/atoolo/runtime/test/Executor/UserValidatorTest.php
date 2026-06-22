<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test\Executor;

use Atoolo\Runtime\Executor\UserValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(UserValidator::class)]
class UserValidatorTest extends TestCase
{
    public function testValidateUserWithInvalidUser(): void
    {
        $userValidator = new UserValidator();
        $this->expectException(RuntimeException::class);
        $userValidator->execute('', [
            'package1' => [
                'users' => ['abc'],
            ],
        ]);
    }

    public function testValidateUser(): void
    {
        $userValidator = new UserValidator();
        $processUser = posix_getpwuid(posix_geteuid())['name'];

        $this->expectNotToPerformAssertions();
        $userValidator->execute('', [
            'package1' => [
                'users' => [$processUser],
            ],
        ]);
    }

    public function testValidateUserWithoutUser(): void
    {
        $userValidator = new UserValidator();
        $this->expectNotToPerformAssertions();
        $userValidator->execute('', [
            'package1' => [
            ],
        ]);
    }

    public function testValidateUserWithNonArray(): void
    {
        $userValidator = new UserValidator();
        $this->expectException(RuntimeException::class);
        $userValidator->execute('', [
            'package1' => [
                'users' => 'non-array',
            ],
        ]);
    }

    public function testValidateUserWithScriptOwner(): void
    {
        $userValidator = new UserValidator();
        $this->expectNotToPerformAssertions();
        $userValidator->execute('', [
            'package1' => [
                'users' => ['{SCRIPT_OWNER}'],
            ],
        ]);
    }
}
