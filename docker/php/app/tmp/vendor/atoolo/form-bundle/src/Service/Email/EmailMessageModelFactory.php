<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Service\FormDataModelFactory;
use Atoolo\Form\Service\Platform;
use Atoolo\Resource\ResourceChannel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EmailMessageModelFactory
{
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $channel,
        private readonly FormDataModelFactory $formDataModelFactory,
        private readonly Platform $platform,
    ) {}

    /**
     * @param FormSubmission $submission
     * @param bool $includeEmptyFields
     * @return EmailMessageModel
     */
    public function create(FormSubmission $submission, bool $includeEmptyFields): array
    {
        $items = $this->formDataModelFactory->create(
            $submission->formDefinition,
            $this->platform->objectToArrayRecursive((array) $submission->data),
            $includeEmptyFields,
        );
        $dateTime = $this->platform->datetime();
        return [
            'lang' => $submission->formDefinition->lang,
            'url' => 'https://' . $this->channel->serverName,
            'tenant' => [ 'name' => $this->channel->tenant->name ],
            'host' => $this->channel->serverName,
            'date' => $dateTime,
            'items' => $items,
        ];
    }
}
