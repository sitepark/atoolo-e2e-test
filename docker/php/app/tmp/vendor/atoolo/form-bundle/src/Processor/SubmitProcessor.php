<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('atoolo_form.processor')]
interface SubmitProcessor
{
    /**
     * @param array<string,mixed> $options
     */
    public function process(FormSubmission $submission, array $options): FormSubmission;
}
