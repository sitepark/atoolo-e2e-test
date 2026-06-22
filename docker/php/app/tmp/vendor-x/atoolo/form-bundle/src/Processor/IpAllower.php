<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTaggedItem(index: 'ip-allower', priority: 100)]
class IpAllower implements SubmitProcessor
{
    /**
     * @param array<string> $allowedIps
     */
    public function __construct(
        #[Autowire(param: 'atoolo_form.processor.ip_allower.allowed_ips')]
        private readonly array $allowedIps,
    ) {}

    /**
     * @param array<string,mixed> $options
     */
    public function process(FormSubmission $submission, array $options): FormSubmission
    {
        if (in_array($submission->remoteAddress, $this->allowedIps, true)) {
            $submission->approved = true;
        }
        return $submission;
    }
}
