<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator\Extended;

use Opis\JsonSchema\Validator;

/**
 * An extension so that the format validator can also be passed the schema.
 * For details see https://github.com/opis/json-schema/issues/142
 */
class ValidatorExtended extends Validator
{
    public function __construct()
    {
        parent::__construct();
        $draft = new Draft202012Extended();
        $this->parser()->addDraft($draft);
        $this->parser()->setDefaultDraftVersion($draft->version());
    }
}
