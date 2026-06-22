<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Exception\LimitExceededException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsTaggedItem(index: 'ip-limiter', priority: 80)]
class IpLimiter implements SubmitProcessor
{
    public function __construct(private readonly RateLimiterFactory $formSubmitByIpLimiter) {}

    /**
     * @param array<string,mixed> $options
     */
    public function process(FormSubmission $submission, array $options): FormSubmission
    {
        if ($submission->approved) {
            return $submission;
        }

        $limiter = $this->formSubmitByIpLimiter->create($submission->remoteAddress);
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new LimitExceededException();
        }

        return $submission;
    }
}
