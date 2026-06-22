<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Exception\LimitExceededException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsTaggedItem(index: 'submit-limiter', priority: 70)]
class SubmitLimiter implements SubmitProcessor
{
    public function __construct(private readonly RateLimiterFactory $formSubmitTotalLimiter) {}

    /**
     * @param array<string,mixed> $options
     */
    public function process(FormSubmission $submission, array $options): FormSubmission
    {
        if ($submission->approved) {
            return $submission;
        }

        $limiter = $this->formSubmitTotalLimiter->create();
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new LimitExceededException();
        }

        return $submission;
    }
}
