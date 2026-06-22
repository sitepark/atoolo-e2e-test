<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

/**
 * @codeCoverageIgnore
 */
enum Type: string
{
    case CONTROL = 'Control';
    case ANNOTATION = 'Annotation'; // Custom type
    case HORIZONTAL_LAYOUT = 'HorizontalLayout';
    case VERTICAL_LAYOUT = 'VerticalLayout';
    case GROUP = 'Group';
    case CATEGORIZATION = 'Categorization';
    case CATEGORY = 'Category';
}
