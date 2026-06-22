<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service;

use Atoolo\WebAccount\Service\ServiceException;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ServiceException::class)]
class ServiceExceptionTest extends TestCase
{
    public function testConstructorWithMessage(): void
    {
        $exception = new ServiceException('Test error message');

        $this->assertEquals('Test error message', $exception->getMessage(), "Exception message should match the provided message");
    }

    public function testConstructorWithMessageAndPreviousException(): void
    {
        $previous = new Exception('Previous exception');
        $exception = new ServiceException('Test error message', $previous);

        $this->assertEquals('Test error message', $exception->getMessage(), "Exception message should match the provided message");
    }

    public function testGetPrevious(): void
    {
        $previous = new Exception('Previous exception');
        $exception = new ServiceException('Test error message', $previous);

        $this->assertSame($previous, $exception->getPrevious(), "Previous exception should be accessible");
    }

    public function testCodeIsAlwaysZero(): void
    {
        $exception = new ServiceException('Test error message');

        $this->assertEquals(0, $exception->getCode(), "Exception code should always be 0");
    }
}
