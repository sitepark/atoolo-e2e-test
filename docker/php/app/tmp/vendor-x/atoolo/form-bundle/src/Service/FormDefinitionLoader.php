<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Exception\FormNotFoundException;
use Atoolo\Form\Exception\InvalidFormConfiguration;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @phpstan-type DelivererModel = array{
 *     modelType?: string,
 *     from?: array<string,string>,
 *     to?: array<string,string>,
 *     cc?: array<string,string>,
 *     bcc?: array<string,string>,
 *     selectable?: array{
 *          to?: array<string,string>
 *     },
 *     subject?: string,
 *     format?: string,
 *     attachCsv?: bool,
 *     showEmpty?: bool
 * }
 * @phpstan-type FormEditorModel = array{
 *     jsonForms?: array{
 *         schema?: JsonSchema,
 *         uischema?: array<string,mixed>
 *     },
 *     bottomBar?: array{
 *         items?: array<array{
 *             value: string,
 *             label: string,
 *         }>,
 *     },
 *     messages?: array<string, array{
 *         headline: string,
 *         text: string,
 *     }>,
 *     deliverer?: DelivererModel,
 * }
 * @phpstan-type FormEditorComponent = array{
 *     id: string,
 *     type: string,
 *     model?: FormEditorModel,
 *     items?: array<string, mixed>,
 * }
 * @phpstan-type DelivererConfig = array{
 *      from: array<array{
 *          address: string,
 *          name: string,
 *      }>,
 *      to: array<array{
 *          address: string,
 *          name: string,
 *      }>,
 *      cc: array<array{
 *          address: string,
 *          name: string,
 *      }>,
 *      bcc: array<array{
 *          address: string,
 *          name: string,
 *      }>,
 *      subject: string,
 *      format: string,
 *      attachCsv: bool,
 *      showEmpty: bool
 *      selectable: array<string, array{
 *         to: array{
 *            address: string,
 *            name: string,
 *        },
 *     }>,
 *  }
 */
class FormDefinitionLoader
{
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_loader')]
        private readonly ResourceLoader $resourceLoader,
        private readonly LabelTranslator $translator,
    ) {}

    public function loadFromResource(ResourceLocation $location, string $component): FormDefinition
    {
        $resource = $this->resourceLoader->load($location);

        /** @var array<FormEditorComponent> $items */
        $items = $resource->data->getArray('content.items');
        $componentConfig = $this->findFormEditorComponent($items, $component);

        if (empty($componentConfig)) {
            throw new FormNotFoundException('Component \'' . $component . '\' not found');
        }

        /** @var FormEditorModel $model */
        $model = $componentConfig['model'] ?? [];
        return $this->loadFromModel($resource->lang->code, $component, $model);
    }

    /**
     * @param string $component
     * @param FormEditorModel $model
     * @return FormDefinition
     */
    public function loadFromModel(string $lang, string $component, array $model): FormDefinition
    {
        if (!isset($model['jsonForms'])) {
            throw new InvalidFormConfiguration('Missing jsonForms definition in component \'' . $component . '\'');
        }

        $jsonForms = $model['jsonForms'];

        if (!isset($jsonForms['schema'])) {
            throw new InvalidFormConfiguration('Missing jsonForms.schema definition in component \'' . $component . '\'');
        }
        if (!isset($jsonForms['uischema'])) {
            throw new InvalidFormConfiguration('Missing jsonForms.uischema definition in component \'' . $component . '\'');
        }

        /** @var array{submit:string} $buttons */
        $buttons = [];
        foreach ($model['bottomBar']['items'] ?? [] as $button) {
            $buttons[$button['value']] = $button['label'];
        }

        $messages = $model['messages'] ?? [];

        if (!isset($model['deliverer']['modelType'])) {
            throw new InvalidFormConfiguration('Missing deliverer definition in component \'' . $component . '\'');
        }

        if ($model['deliverer']['modelType'] !== 'content.form.deliverer.email') {
            throw new InvalidFormConfiguration('Unsupported deliverer \'' . $model['deliverer']['modelType'] . '\' in component \'' . $component . '\'');
        }

        $deliverer = $model['deliverer'];
        $processor = [
            'email-sender' => $this->transformProcessorConfig($deliverer),
        ];

        /** @var JsonSchema $schema */
        $schema = $this->translator->translate($jsonForms['schema']);
        $uiSchema = $this->translator->translate($jsonForms['uischema']);

        return new FormDefinition(
            schema: $schema,
            uischema: $this->deserializeUiSchema($uiSchema),
            data: null,
            buttons: $buttons,
            messages: $messages,
            lang: $lang,
            component: $component,
            processors: $processor,
        );
    }

    /**
     * @param DelivererModel $deliverer
     * @return DelivererConfig
     */
    private function transformProcessorConfig(array $deliverer): array
    {
        $from = [];
        foreach ($deliverer['from'] ?? [] as $address => $name) {
            $from[] = ['address' => $address, 'name' => $name];
        }

        $to = [];
        foreach ($deliverer['to'] ?? [] as $address => $name) {
            $to[] = ['address' => $address, 'name' => $name];
        }

        $cc = [];
        foreach ($deliverer['cc'] ?? [] as $address => $name) {
            $cc[] = ['address' => $address, 'name' => $name];
        }

        $bcc = [];
        foreach ($deliverer['bcc'] ?? [] as $address => $name) {
            $bcc[] = ['address' => $address, 'name' => $name];
        }

        $selectable = [];
        foreach ($deliverer['selectable'] ?? [] as $key => $config) {
            foreach ($config['to'] as $address => $name) {
                $selectable[$key]['to'][] = ['address' => $address, 'name' => $name];
            }
        }

        return [
            'from' => $from,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $deliverer['subject'] ?? '',
            'format' => $deliverer['format'] ?? 'html',
            'attachCsv' => $deliverer['attachCsv'] ?? false,
            'showEmpty' => $deliverer['showEmpty'] ?? false,
            'selectable' => $selectable,
        ];
    }


    /**
     * @param array<string,mixed> $data
     * @return Layout
     */
    private function deserializeUiSchema(array $data): Layout
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

        $encoders = [new JsonEncoder()];
        $normalizers = [ new ArrayDenormalizer(), new BackedEnumNormalizer(), new ObjectNormalizer(
            classMetadataFactory: $classMetadataFactory,
            propertyTypeExtractor: new PhpDocExtractor(),
            classDiscriminatorResolver: $discriminator,
        )];
        /** @var Layout $layout */
        $layout = (new Serializer($normalizers, $encoders))->denormalize($data, Layout::class);
        return $layout;
    }

    /**
     * @param array<FormEditorComponent> $items
     * @param string $component
     * @return FormEditorComponent|array<void>
     */
    private function findFormEditorComponent(array $items, string $component): array
    {
        foreach ($items as $item) {
            if ($item['type'] === 'formContainer' && $item['id'] === $component) {
                return $item;
            }
            /** @var array<FormEditorComponent> $children */
            $children = $item['items'] ?? [];
            $result = $this->findFormEditorComponent($children, $component);
            if ($result) {
                return $result;
            }
        }
        return [];
    }
}
