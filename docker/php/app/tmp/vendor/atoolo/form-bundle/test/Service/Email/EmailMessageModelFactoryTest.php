<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Dto\UISchema\Type;
use Atoolo\Form\Service\Email\EmailMessageModelFactory;
use Atoolo\Form\Service\FormDataModelFactory;
use Atoolo\Form\Service\Platform;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(EmailMessageModelFactory::class)]
class EmailMessageModelFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreate(): void
    {
        $tenant = new ResourceTenant(
            '',
            'Test Tenant',
            '',
            '',
            new DataBag([]),
        );
        $channel = new ResourceChannel(
            '',
            '',
            '',
            'test.example.com',
            false,
            '',
            '',
            '',
            '',
            '',
            '',
            [],
            new DataBag([]),
            $tenant,
        );

        $formDataModelFactory = $this->createStub(FormDataModelFactory::class);
        $formDataModelFactory->method('create')
            ->willReturn([
                'dummy' => true,
            ]);
        $platform = $this->createStub(Platform::class);
        $dateTime = new DateTime();
        $dateTime->setDate(23, 9, 2024);
        $dateTime->setTime(9, 38, 20);
        $platform->method('datetime')->willReturn($dateTime);
        $factory = new EmailMessageModelFactory($channel, $formDataModelFactory, $platform);

        $formDefinition = new FormDefinition(
            schema: [],
            uischema: new Layout(Type::VERTICAL_LAYOUT),
            data: [],
            buttons: [],
            messages: [],
            lang: 'en',
            component: 'test',
            processors: [],
        );

        $data = new stdClass();
        $submission = new FormSubmission('127.0.0.1', $formDefinition, $data);

        $expected = [
            'lang' => 'en',
            'url' => 'https://test.example.com',
            'tenant' => ['name' => 'Test Tenant'],
            'host' => 'test.example.com',
            'date' => $dateTime,
            'items' => ['dummy' => true],
        ];

        $this->assertEquals($expected, $factory->create($submission, true), 'unexpected model');
    }
}
