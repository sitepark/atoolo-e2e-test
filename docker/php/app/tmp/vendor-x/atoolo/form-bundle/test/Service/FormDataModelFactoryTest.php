<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Dto\FormData\UploadFile;
use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Service\DataUrlParser;
use Atoolo\Form\Service\FormDataModelFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(FormDataModelFactory::class)]
class FormDataModelFactoryTest extends TestCase
{
    private static string $RESOURCE_PATH = __DIR__ . '/../resources/Service/FormDataModelFactoryTest';
    private DataUrlParser $dataUrlParser;

    public TranslatorInterface $translator;

    public FormDataModelFactory $formDataModelFactory;

    public function setUp(): void
    {
        $this->dataUrlParser = $this->createStub(DataUrlParser::class);
        $this->dataUrlParser->method('parse')
            ->willReturn(new UploadFile(
                'text.txt',
                'text/plain',
                'text',
                4,
            ));
        $this->formDataModelFactory = new FormDataModelFactory($this->dataUrlParser);
    }

    public static function loadTextures(): array
    {
        $textures = [];
        $files = glob(self::$RESOURCE_PATH . '/*.php');
        foreach ($files as $file) {
            $textures[basename($file)] = [require $file];
        }

        return $textures;
    }

    #[DataProvider('loadTextures')]
    public function testCreate(array $texture): void
    {

        $schema = $texture['schema'];
        $uischema = $texture['uischema'];
        $data = $texture['data'];
        $includeEmptyFields = $texture['includeEmptyFields'] ?? false;
        $expected = $texture['expected'];

        $model = $this->createModel($schema, $uischema, $data, $includeEmptyFields);
        $this->assertEquals($expected, $model, 'unexpected model');
    }

    private function createModel(
        array $schema,
        array $uischema,
        array $data,
        bool $includeEmptyFields = false,
    ): array {
        $formDefinition = new FormDefinition(
            schema : $schema,
            uischema: $this->deserializeUiSchema($uischema),
            data: $data,
            buttons: [],
            messages: null,
            lang: '',
            component: '',
            processors: null,
        );
        return $this->formDataModelFactory->create($formDefinition, $data, $includeEmptyFields);
    }

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
        return (new Serializer($normalizers, $encoders))->denormalize($data, Layout::class);
    }

}
