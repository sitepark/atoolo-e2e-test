<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Service\JsonSchemaValidator\Constraint;
use Atoolo\Form\Service\JsonSchemaValidator\Extended\ValidatorExtended;
use Atoolo\Form\Service\JsonSchemaValidator\FormatConstraint;
use InvalidArgumentException;
use JsonException;
use LogicException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Validator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class JsonSchemaValidator
{
    /**
     * @param iterable<Constraint> $constraints
     */
    public function __construct(
        #[Autowire('@' . ValidatorExtended::class)]
        private readonly Validator $validator,
        #[AutowireIterator('atoolo_form.jsonSchemaConstraint')]
        iterable $constraints,
        private readonly Platform $platform,
    ) {
        foreach ($constraints as $constraint) {
            $this->registerConstraint($constraint);
        }
    }

    private function registerConstraint(Constraint $constraint): void
    {
        if ($constraint instanceof FormatConstraint) {
            $this->registerFormatConstraint($constraint);
        } else {
            throw new InvalidArgumentException('Unknown constraint type ' . get_class($constraint));
        }
    }

    private function registerFormatConstraint(FormatConstraint $constraint): void
    {
        $formatResolver = $this->validator->parser()->getFormatResolver();
        if ($formatResolver === null) {
            throw new LogicException('No format resolver found');
        }

        $type = $constraint->getType();
        $name = $constraint->getName();

        $formatResolver->registerCallable(
            $type,
            $name,
            function ($data, $schema) use ($constraint) {
                return $constraint->check($data, $schema);
            },
        );
    }


    /**
     * @param JsonSchema $schema
     * @throws JsonException
     */
    public function validate(array $schema, object $data): void
    {
        $schemaJson = $this->platform->arrayToObjectRecursive($schema);

        $result = $this->validator->validate($data, $schemaJson);

        if (!$result->isValid() && $result->error() !== null) {
            throw $this->errorsToValidationFailedException($data, $result->error());
        }
    }

    private function errorsToValidationFailedException(object $data, ValidationError $validationError): ValidationFailedException
    {
        $list = new ConstraintViolationList();
        $formatter = new ErrorFormatter();
        $errors = $formatter->format($validationError, false, function (ValidationError $error) use ($formatter) {
            $schema = $error->schema()->info();

            $path = $schema->path();
            $args = $error->args();
            if (isset($args['missing'][0])) {
                $path[] = $args['missing'][0];
            }

            return [
                'message' => $formatter->formatErrorMessage($error),
                'path' => implode('/', $path),
                'args' => $error->args(),
                'constraint' => $error->keyword(),
            ];
        });
        foreach ($errors as $error) {
            $v = new ConstraintViolation(
                $error['message'],
                null,
                $error['args'],
                null,
                $error['path'],
                '',
                null,
                null,
                $error['constraint'] === 'require' ? new NotBlank() : null,
            );
            $list->add($v);
        }
        throw new ValidationFailedException($data, $list);
    }
}
