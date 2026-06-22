<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Control;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Dto\UISchema\Type;
use Atoolo\Form\Exception\FormNotFoundException;
use Atoolo\Form\Exception\InvalidFormConfiguration;
use Atoolo\Form\Service\FormDefinitionLoader;
use Atoolo\Form\Service\LabelTranslator;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormDefinitionLoader::class)]
class FormDefinitionLoaderTest extends TestCase
{
    private ResourceLoader $resourceLoader;

    private FormDefinitionLoader $loader;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->resourceLoader = $this->createStub(ResourceLoader::class);
        $translator = $this->createStub(LabelTranslator::class);
        $translator->method('translate')
            ->willReturnCallback(function (array $schema) {
                return $schema;
            });
        $this->loader = new FormDefinitionLoader(
            $this->resourceLoader,
            $translator,
        );
    }

    public function testFormNotFound(): void
    {
        $this->setContentForResourceLoaderStub([]);

        $this->expectException(FormNotFoundException::class);
        $this->loader->loadFromResource(ResourceLocation::of('/test.php'), 'formEditor-1');
    }

    public function testMissingJsonForm(): void
    {
        $this->setContentForResourceLoaderStub([
            "items" => [[
                "type" => "formContainer",
                "id" => "formEditor-1",
                "model" => [
                ],
            ]],
        ]);
        $this->expectException(InvalidFormConfiguration::class);
        $this->loader->loadFromResource(ResourceLocation::of('/test.php'), 'formEditor-1');
    }

    public function testMissingSchema(): void
    {
        $this->setContentForResourceLoaderStub([
            "items" => [[
                "type" => "formContainer",
                "id" => "formEditor-1",
                "model" => [
                    "jsonForms" => [
                        "uischema" => [],
                    ],
                ],
            ]],
        ]);
        $this->expectException(InvalidFormConfiguration::class);
        $this->loader->loadFromResource(ResourceLocation::of('/test.php'), 'formEditor-1');
    }

    public function testMissingUiSchema(): void
    {
        $this->setContentForResourceLoaderStub([
            "items" => [[
                "type" => "formContainer",
                "id" => "formEditor-1",
                "model" => [
                    "jsonForms" => [
                        "schema" => [],
                    ],
                ],
            ]],
        ]);
        $this->expectException(InvalidFormConfiguration::class);
        $this->loader->loadFromResource(ResourceLocation::of('/test.php'), 'formEditor-1');
    }

    public function testMissingDeliverer(): void
    {
        $this->setContentForResourceLoaderStub([
            "items" => [[
                "type" => "formContainer",
                "id" => "formEditor-1",
                "model" => [
                    "jsonForms" => [
                        "schema" => [],
                        "uischema" => [],
                    ],
                ],
            ]],
        ]);
        $this->expectException(InvalidFormConfiguration::class);
        $this->loader->loadFromResource(ResourceLocation::of('/test.php'), 'formEditor-1');
    }

    public function testUnsupportedDeliverer(): void
    {
        $this->setContentForResourceLoaderStub([
            "items" => [[
                "type" => "formContainer",
                "id" => "formEditor-1",
                "model" => [
                    "jsonForms" => [
                        "schema" => [],
                        "uischema" => [],
                    ],
                    "deliverer" => [
                        'modelType' => 'unsupported',
                    ],
                ],
            ]],
        ]);
        $this->expectException(InvalidFormConfiguration::class);
        $this->loader->loadFromResource(ResourceLocation::of('/test.php'), 'formEditor-1');
    }

    public function testLoad(): void
    {
        $this->setContentForResourceLoaderStub([
            "type" => "ROOT",
            "id" => "ROOT",
            "items" => [[
                "type" => "main",
                "id" => "main",
                "items" => [[
                    "type" => "implicitSection",
                    "model" => [
                        "implicit" => true,
                    ],
                    "id" => "implicitSection-1",
                    "items" => [[
                        "type" => "formContainer",
                        "id" => "formEditor-1",
                        "model" => [
                            "headline" => "Form heading",
                            "https" => true,
                            "consent" => false,
                            "jsonForms" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "field" => [
                                            "type" => "string",
                                            "title" => "Single-line text field",
                                        ],
                                    ],
                                ],
                                "uischema" => [
                                    "type" => "VerticalLayout",
                                    "elements" => [
                                        [
                                            "type" => "Control",
                                            "scope" => "#/properties/field",
                                            "label" => "Single-line text field",
                                            "options" => [
                                                "autocomplete" => "name",
                                                "spaceAfter" => true,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            "bottomBar" => [
                                "items" => [[
                                    "type" => "button.submit",
                                    "id" => "button-1",
                                    "label" => "submit",
                                    "value" => "submit",
                                    "action" => "submit",
                                ]],
                            ],
                            "deliverer" => [
                                "modelType" => "content.form.deliverer.email",
                                "from" => [
                                    "test@email.com" => "Sender",
                                ],
                                "to" => [
                                    "test@email.com" => "to address",
                                ],
                                "cc" => [
                                    "test@email.com" => "cc address",
                                ],
                                "bcc" => [
                                    "test@email.com" => "bcc address",
                                ],
                                "subject" => "Subject of E-Mail",
                                "type" => "email",
                                "format" => "plain",
                                "attachCsv" => false,
                                "showEmpty" => false,
                            ],
                            "messages" => [
                                "success" => [
                                    "headline" => "Confirmation",
                                    "text" => "Text",
                                ],
                            ],
                        ],
                    ]],
                ]],
            ]],
        ]);

        $definition = $this->loader->loadFromResource(ResourceLocation::of('/test.php'), 'formEditor-1');

        $uischema = new Layout(
            type: Type::VERTICAL_LAYOUT,
            elements: [(new Control(
                scope: '#/properties/field',
                label: 'Single-line text field',
                options: [
                    'autocomplete' => 'name',
                    'spaceAfter' => true,
                ],
            ))],
        );
        $expected = new FormDefinition(
            schema: [
                'type' => 'object',
                'properties' => [
                    "field" => [
                        "type" => "string",
                        "title" => "Single-line text field",
                    ],
                ],
            ],
            uischema: $uischema,
            data: null,
            buttons: [
                'submit' => 'submit',
            ],
            messages: [
                'success' => [
                    'headline' => 'Confirmation',
                    'text' => 'Text',
                ],
            ],
            lang: 'en',
            component: 'formEditor-1',
            processors: [
                'email-sender' => [
                    'from' => [
                        [
                            'address' => 'test@email.com',
                            'name' => 'Sender',
                        ],
                    ],
                    'to' => [
                        [
                            'address' => 'test@email.com',
                            'name' => 'to address',
                        ],
                    ],
                    'cc' => [
                        [
                            'address' => 'test@email.com',
                            'name' => 'cc address',
                        ],
                    ],
                    'bcc' => [
                        [
                            'address' => 'test@email.com',
                            'name' => 'bcc address',
                        ],
                    ],
                    'subject' => 'Subject of E-Mail',
                    'format' => 'plain',
                    'attachCsv' => false,
                    'showEmpty' => false,
                    'selectable' => [],
                ],
            ],
        );

        $this->assertEquals($expected, $definition, 'FormDefinition does not match');
    }

    private function setContentForResourceLoaderStub(array $content): void
    {
        $this->resourceLoader->method('load')->willReturn(new Resource(
            '/test',
            '',
            '',
            '',
            ResourceLanguage::of('en'),
            new DataBag(['content' => $content]),
        ));
    }
}
