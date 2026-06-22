<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('atoolo_form.jsonSchemaConstraint')]
interface Constraint {}
