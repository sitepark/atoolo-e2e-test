<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\GraphQL\Resolver\Resource;

use Atoolo\EventsCalendar\Dto\Scheduling\Scheduling;
use Atoolo\EventsCalendar\Service\GraphQL\Factory\SchedulingFactory;
use Atoolo\EventsCalendar\Service\GraphQL\Resolver\Resource\ResourceSchedulingResolver;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLanguage;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceSchedulingResolver::class)]
class ResourceSchedulingResolverTest extends TestCase
{
    private ResourceSchedulingResolver $resolver;

    private SchedulingFactory&MockObject $schedulingFactory;

    public function setUp(): void
    {
        $this->schedulingFactory = $this->createMock(
            SchedulingFactory::class,
        );
        $this->resolver = new ResourceSchedulingResolver(
            $this->schedulingFactory,
        );
    }

    public function testGetSchedulings(): void
    {
        $resource = $this->createResource([]);
        $schedulingsExpected = [
            new Scheduling(
                new DateTime(),
                null,
                false,
                true,
                false,
                null,
            ),
        ];
        $this->schedulingFactory
            ->method('create')
            ->willReturn($schedulingsExpected);
        $schedulings = $this->resolver->getSchedulings($resource);
        $this->assertNotEmpty($schedulings);
        $this->assertEquals(
            $schedulingsExpected,
            $schedulings,
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private function createResource(array $data): Resource
    {
        return new Resource(
            $data['url'] ?? '',
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['objectType'] ?? '',
            ResourceLanguage::default(),
            new DataBag($data),
        );
    }
}
