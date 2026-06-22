<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

/**
 * @codeCoverageIgnore
 */
#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'Annotation' => Annotation::class,
    'Control' => Control::class,
    'Group' => Layout::class,
    'HorizontalLayout' => Layout::class,
    'VerticalLayout' => Layout::class,
    'Categorization' => Layout::class,
    'Category' => Layout::class,
])]
abstract class Element
{
    public function __construct(
        public readonly Type $type,
        public ?Role $rule = null,
    ) {}
}
