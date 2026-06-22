<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Exception\AccessDeniedException;
use Atoolo\Form\Exception\LimitExceededException;
use Atoolo\Form\Exception\SpamDetectedException;
use Phpro\ApiProblem\ApiProblemInterface;
use Phpro\ApiProblem\Http\HttpApiProblem;
use Phpro\ApiProblem\Http\ValidationApiProblem;
use Phpro\ApiProblemBundle\Transformer\ExceptionTransformerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/**
 * @implements ExceptionTransformerInterface<RuntimeException>
 */
#[AutoconfigureTag('phpro.api_problem.exception_transformer')]
class ExceptionTransformer implements ExceptionTransformerInterface
{
    public function transform(Throwable $exception): ApiProblemInterface
    {
        if ($exception instanceof ValidationFailedException) {
            return new ValidationApiProblem($exception->getViolations());
        }

        if ($exception instanceof LimitExceededException) {
            return new HttpApiProblem(Response::HTTP_TOO_MANY_REQUESTS, [
                'detail' => 'The limit has been reached. The request can be repeated at a later time',
            ]);
        }

        if ($exception instanceof AccessDeniedException) {
            return new HttpApiProblem(Response::HTTP_FORBIDDEN, [
                'detail' => 'Access was denied',
            ]);
        }

        if ($exception instanceof SpamDetectedException) {
            return new HttpApiProblem(Response::HTTP_UNPROCESSABLE_ENTITY, [
                'detail' => 'Spam detected',
            ]);
        }


        throw new \LogicException('Unaccepted exception ' . get_class($exception));
    }

    public function accepts(Throwable $exception): bool
    {
        return $exception instanceof ValidationFailedException
            || $exception instanceof LimitExceededException
            || $exception instanceof AccessDeniedException
            || $exception instanceof SpamDetectedException;
    }
}
