<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

/**
 * @codeCoverageIgnore
 */
class MjmlRenderer
{
    public function render(string $mjml): string
    {
        // TODO: use mjml template engine to render html from mjml
        $html = $mjml;
        return $html;
    }
}
