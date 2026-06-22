<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Not yet implemented
 */
#[AsTaggedItem(index: 'spam-detector', priority: 60)]
class SpamDetector implements SubmitProcessor
{
    /**
     * @param array<string,mixed> $options
     */
    public function process(FormSubmission $submission, array $options): FormSubmission
    {
        if ($submission->approved) {
            return $submission;
        }

        return $submission;
    }
}
