<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Exception\AccessDeniedException;
use Atoolo\Form\Exception\LimitExceededException;
use Atoolo\Form\Exception\SpamDetectedException;
use Atoolo\Form\Service\ExceptionTransformer;
use Exception;
use LogicException;
use Phpro\ApiProblem\Http\HttpApiProblem;
use Phpro\ApiProblem\Http\ValidationApiProblem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

#[CoversClass(ExceptionTransformer::class)]
class ExceptionTransformerTest extends TestCase
{
    private ExceptionTransformer $transformer;

    public function setUp(): void
    {
        $this->transformer = new ExceptionTransformer();
    }

    public static function transformTestData(): array
    {
        return [
            [ ValidationFailedException::class, ValidationApiProblem::class ],
            [ LimitExceededException::class, HttpApiProblem::class ],
            [ AccessDeniedException::class, HttpApiProblem::class ],
            [ SpamDetectedException::class, HttpApiProblem::class ],
            [ Exception::class, LogicException::class, false ],
        ];
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[DataProvider('transformTestData')]
    public function testAccepts(
        string $exceptionClass,
        string $transformedClass,
        bool $accepted = true,
    ): void {
        /** @var Throwable $exception */
        $exception = $this->createStub($exceptionClass);

        $this->assertEquals(
            $accepted,
            $this->transformer->accepts($exception),
            'unexpected accepts result',
        );
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[DataProvider('transformTestData')]
    public function testTransform(
        string $exceptionClass,
        string $transformedClass,
        bool $validException = true,
    ): void {

        /** @var Throwable $exception */
        $exception = $this->createStub($exceptionClass);

        if (!$validException) {
            $this->expectException(LogicException::class);
        }

        $transformed = $this->transformer->transform($exception);

        if ($validException) {
            $this->assertEquals(
                $transformedClass,
                get_class($transformed),
                'unexpected class: ' . get_class($transformed),
            );
        }
    }
}
