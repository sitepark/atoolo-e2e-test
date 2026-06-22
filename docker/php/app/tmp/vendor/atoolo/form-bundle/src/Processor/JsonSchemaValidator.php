<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use JsonException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsTaggedItem(index: 'json-schema-validator', priority: 20)]
class JsonSchemaValidator implements SubmitProcessor
{
    public function __construct(private readonly \Atoolo\Form\Service\JsonSchemaValidator $validator) {}

    /**
     * @param array<string,mixed> $options
     * @throws ValidationFailedException
     * @throws JsonException
     */
    public function process(FormSubmission $submission, array $options): FormSubmission
    {
        $this->validator->validate($submission->formDefinition->schema, $submission->data);
        return $submission;
    }
}
