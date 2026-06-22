<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Controller;

use Atoolo\Form\Controller\FormController;
use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Dto\UISchema\Type;
use Atoolo\Form\Exception\FormNotFoundException;
use Atoolo\Form\Service\FormDefinitionLoader;
use Atoolo\Form\Service\SubmitHandler;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Resource\ResourceTenant;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Translation\LocaleSwitcher;

#[CoversClass(FormController::class)]
class FormControllerTest extends TestCase
{
    private ResourceChannel $channel;

    private FormDefinitionLoader $formDefinitionLoader;

    private SubmitHandler $submitHandler;

    public LocaleSwitcher $localeSwitcher;

    public static function resourceLocationOfDefinitionProvider(): array
    {
        return [
            ['de_DE', [], '', '', '/location.php'],
            ['de_DE', [], 'de', '', '/location.php'],
            ['de_DE', ['en_US'], '', '', '/location.php'],
            ['de_DE', ['en_US'], 'en', 'en', '/location.php'],
            ['de_DE', ['en_US'], 'it', '', '/it/location.php'],
        ];
    }

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->channel = $this->createResourceChannel('de_DE', []);
        $this->formDefinitionLoader = $this->createMock(FormDefinitionLoader::class);
        $this->submitHandler = $this->createMock(SubmitHandler::class);
        $this->localeSwitcher = $this->createMock(LocaleSwitcher::class);

    }

    /**
     * @throws Exception
     */
    private function createController(): FormController
    {
        $controller = new FormController(
            $this->channel,
            $this->formDefinitionLoader,
            $this->submitHandler,
            $this->localeSwitcher,
        );
        $controller->setContainer($this->createStub(ContainerInterface::class));
        return $controller;
    }

    /**
     * @throws Exception
     */
    #[DataProvider('resourceLocationOfDefinitionProvider')]
    public function testResourceLocationOfDefinitionWithEmptyLang(
        string $defaultLang,
        array $translationLocales,
        string $requestLang,
        string $locationLang,
        string $locationPath,
    ): void {
        $this->channel = $this->createResourceChannel($defaultLang, $translationLocales);
        $this->formDefinitionLoader->expects($this->once())
            ->method('loadFromResource')
            ->with(
                ResourceLocation::of(
                    $locationPath,
                    ResourceLanguage::of($locationLang),
                ),
                'form-1',
            );
        $controller = $this->createController();
        $controller->definition($requestLang, 'location', 'form-1');
    }


    /**
     * @throws Exception
     */
    public function testDefinitionWithResourceNotFoundException(): void
    {
        $this->formDefinitionLoader
            ->method('loadFromResource')
            ->willThrowException(new ResourceNotFoundException(ResourceLocation::of('test')));

        $controller = $this->createController();

        $this->expectException(NotFoundHttpException::class);
        $controller->definition('', 'location', 'form-1');
    }
    /**
     * @throws Exception
     */
    public function testDefinitionWithFormNotFoundException(): void
    {
        $this->formDefinitionLoader
            ->method('loadFromResource')
            ->willThrowException(new FormNotFoundException());

        $controller = $this->createController();

        $this->expectException(NotFoundHttpException::class);
        $controller->definition('', 'location', 'form-1');
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testGetDefinition(): void
    {
        $this->formDefinitionLoader->method('loadFromResource')->willReturn(new FormDefinition(
            schema: [],
            uischema: new Layout(Type::VERTICAL_LAYOUT, []),
            data: null,
            buttons: [],
            messages: null,
            lang: 'en',
            component: 'form-1',
            processors: [
                'processora' => ['options' => 'value'],
            ],
        ));
        $controller = $this->createController();

        $response = $controller->definition('en', 'location', 'form-1');
        $json = [
            "schema" => [],
            "uischema" => [
                "elements" => [],
                "options" => [],
                "type" => "VerticalLayout",
            ],
            "buttons" => [],
            "lang" => "en",
            "component" => "form-1",
        ];
        $this->assertEquals(
            $json,
            json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR),
            "unexpected response",
        );
    }

    /**
     * @throws Exception
     */
    public function testSubmit(): void
    {
        $controller = $this->createController();

        $request = $this->createStub(Request::class);
        $request->method('getContentTypeFormat')->willReturn('json');
        $request->method('getContent')->willReturn('{"field-1":"value"}');
        $request->method('getClientIp')->willReturn('127.0.0.1');

        $response = $controller->submit(
            '',
            'location',
            'form-1',
            $request,
        );
        $this->assertEquals(
            '{"status":200}',
            $response->getContent(),
            "unexpected response",
        );
    }

    /**
     * @throws Exception
     */
    public function testSubmitWithUnsupportedMediaType(): void
    {
        $controller = $this->createController();

        $request = $this->createStub(Request::class);
        $request->method('getContentTypeFormat')->willReturn('other');

        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $controller->submit(
            '',
            'location',
            'form-1',
            $request,
        );
    }

    /**
     * @throws Exception
     */
    public function testSubmitWithEmptyRequest(): void
    {

        $controller = $this->createController();

        $request = $this->createStub(Request::class);
        $request->method('getContentTypeFormat')->willReturn('json');
        $request->method('getContent')->willReturn('');

        $this->expectException(\Symfony\Component\HttpFoundation\Exception\JsonException::class);
        $controller->submit(
            '',
            'location',
            'form-1',
            $request,
        );
    }

    /**
     * @throws Exception
     */
    public function testSubmitWithJsonArrayRequestBody(): void
    {

        $controller = $this->createController();

        $request = $this->createStub(Request::class);
        $request->method('getContentTypeFormat')->willReturn('json');
        $request->method('getContent')->willReturn('["test"]');

        $this->expectException(\Symfony\Component\HttpFoundation\Exception\JsonException::class);
        $controller->submit(
            '',
            'location',
            'form-1',
            $request,
        );
    }

    /**
     * @throws Exception
     */
    public function testSubmitWithInvalidJson(): void
    {

        $controller = $this->createController();

        $request = $this->createStub(Request::class);
        $request->method('getContentTypeFormat')->willReturn('json');
        $request->method('getContent')->willReturn('test');

        $this->expectException(\Symfony\Component\HttpFoundation\Exception\JsonException::class);
        $controller->submit(
            '',
            'location',
            'form-1',
            $request,
        );
    }

    /**
     * @throws Exception
     */
    private function createResourceChannel(string $locale, array $translationLocales): ResourceChannel
    {
        return new ResourceChannel(
            '',
            '',
            '',
            '',
            false,
            '',
            $locale,
            '',
            '',
            '',
            '',
            $translationLocales,
            new DataBag([]),
            $this->createStub(ResourceTenant::class),
        );
    }
}
