<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Controller;

use Atoolo\EventsCalendar\Controller\ICalController;
use Atoolo\EventsCalendar\Service\ICal\ICalFactory;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Resource\ResourceTenant;
use Atoolo\Search\Dto\Search\Query\Filter\IdFilter;
use Atoolo\Search\Dto\Search\Query\SearchQuery;
use Atoolo\Search\Dto\Search\Query\SearchQueryBuilder;
use Atoolo\Search\Dto\Search\Result\SearchResult;
use Atoolo\Search\Search;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

use function PHPUnit\Framework\once;

#[CoversClass(ICalController::class)]
class ICalControllerTest extends TestCase
{
    private ICalController $controller;

    private ResourceLoader&MockObject $resourceLoader;

    private ICalFactory&MockObject $iCalFactory;

    private Search&MockObject $search;

    private SerializerInterface&MockObject $serializer;

    public function setUp(): void
    {
        $this->resourceLoader = $this->createMock(
            ResourceLoader::class,
        );
        $this->iCalFactory = $this->createMock(
            ICalFactory::class,
        );
        $this->search = $this->createMock(
            Search::class,
        );
        $this->serializer = $this->createMock(
            SerializerInterface::class,
        );
        $resourceChannel = $this->createResourceChannel([
            'locale' => 'de_DE',
            'translationLocales' => ['en_US'],
        ]);
        $this->controller = new ICalController(
            $resourceChannel,
            $this->resourceLoader,
            $this->iCalFactory,
            $this->search,
            $this->serializer,
        );
    }

    public function testICalLocation(): void
    {
        $location = 'some/location';
        $resource = $this->createResource([
            'url' => $location,
        ]);
        $this->resourceLoader
            ->expects(once())
            ->method('load')
            ->with(ResourceLocation::of('/' . $location . '.php'))
            ->willReturn($resource);
        $this->iCalFactory
            ->expects(once())
            ->method('createCalendarFromResourcesAsString')
            ->with([$resource])
            ->willReturn('Totally valid calendar data');
        $response = $this->controller->iCalByLocation(
            $location,
            new Request(['occurrence' => '21.03.2025 12:00']),
        );
        $this->assertEquals(
            200,
            $response->getStatusCode(),
        );
        $this->assertEquals(
            'text/calendar',
            $response->headers->get('Content-Type'),
        );
        $this->assertEquals(
            'Totally valid calendar data',
            $response->getContent(),
        );
    }

    public function testICalByLangAndLocation(): void
    {
        $location = 'some/location';
        $resource = $this->createResource([
            'url' => $location,
        ]);
        $this->resourceLoader
            ->expects(once())
            ->method('load')
            ->with(ResourceLocation::of('/' . $location . '.php'))
            ->willReturn($resource);
        $this->iCalFactory
            ->expects(once())
            ->method('createCalendarFromResourcesAsString')
            ->with([$resource])
            ->willReturn('Totally valid calendar data');
        $response = $this->controller->iCalByLangAndLocation(
            'de',
            $location,
            new Request(['occurrence' => '21.03.2025 12:00']),
        );
        $this->assertEquals(
            200,
            $response->getStatusCode(),
        );
        $this->assertEquals(
            'text/calendar',
            $response->headers->get('Content-Type'),
        );
        $this->assertEquals(
            'Totally valid calendar data',
            $response->getContent(),
        );
    }

    public function testICalByLangAndLocationWithLanguage(): void
    {
        $lang = 'en';
        $location = 'some/location';
        $resource = $this->createResource([
            'url' => $location,
            'lang' => ResourceLanguage::of($lang),
        ]);
        $this->resourceLoader
            ->expects(once())
            ->method('load')
            ->with(ResourceLocation::of('/' . $location . '.php', ResourceLanguage::of($lang)))
            ->willReturn($resource);
        $this->iCalFactory
            ->expects(once())
            ->method('createCalendarFromResourcesAsString')
            ->with([$resource])
            ->willReturn('Totally valid calendar data');
        $response = $this->controller->iCalByLangAndLocation($lang, $location, new Request());
        $this->assertEquals(
            200,
            $response->getStatusCode(),
        );
        $this->assertEquals(
            'text/calendar',
            $response->headers->get('Content-Type'),
        );
        $this->assertEquals(
            'Totally valid calendar data',
            $response->getContent(),
        );
    }

    public function testICalByLangAndLocationWithoutLanguage(): void
    {
        $locationA = 'some';
        $locationB = 'location';
        $location = $locationA . '/' . $locationB;
        $resource = $this->createResource([
            'url' => $location,
        ]);
        $this->resourceLoader
            ->expects(once())
            ->method('load')
            ->with(ResourceLocation::of('/' . $location . '.php'))
            ->willReturn($resource);
        $this->iCalFactory
            ->expects(once())
            ->method('createCalendarFromResourcesAsString')
            ->with([$resource])
            ->willReturn('Totally valid calendar data');
        $response = $this->controller->iCalByLangAndLocation($locationA, $locationB, new Request());
        $this->assertEquals(
            200,
            $response->getStatusCode(),
        );
        $this->assertEquals(
            'text/calendar',
            $response->headers->get('Content-Type'),
        );
        $this->assertEquals(
            'Totally valid calendar data',
            $response->getContent(),
        );
    }

    public function testICalByLangAndLocationWithEmptyLanguage(): void
    {
        $locationA = '';
        $locationB = 'location';
        $location = $locationA . '/' . $locationB;
        $resource = $this->createResource([
            'url' => $location,
        ]);
        $this->resourceLoader
            ->expects(once())
            ->method('load')
            ->with(ResourceLocation::of('/' . $locationB . '.php'))
            ->willReturn($resource);
        $this->iCalFactory
            ->expects(once())
            ->method('createCalendarFromResourcesAsString')
            ->with([$resource])
            ->willReturn('Totally valid calendar data');
        $response = $this->controller->iCalByLangAndLocation($locationA, $locationB, new Request());
        $this->assertEquals(
            200,
            $response->getStatusCode(),
        );
        $this->assertEquals(
            'text/calendar',
            $response->headers->get('Content-Type'),
        );
        $this->assertEquals(
            'Totally valid calendar data',
            $response->getContent(),
        );
    }

    public function testICalByLangAndLocationNotFound(): void
    {
        $location = 'some/location';
        $this->resourceLoader
            ->expects(once())
            ->method('load')
            ->with(ResourceLocation::of('/' . $location . '.php'))
            ->willThrowException(
                new ResourceNotFoundException(ResourceLocation::of($location)),
            );
        $this->expectException(NotFoundHttpException::class);
        $this->controller->iCalByLangAndLocation('de', $location, new Request());
    }

    public function testICalByLangAndLocationInvalidResource(): void
    {
        $location = 'some/location';
        $this->resourceLoader
            ->expects(once())
            ->method('load')
            ->with(ResourceLocation::of('/' . $location . '.php'))
            ->willThrowException(
                new InvalidResourceException(ResourceLocation::of($location)),
            );
        $this->expectException(HttpException::class);
        $this->controller->iCalByLangAndLocation('de', $location, new Request());
    }

    public function testICalBySearch(): void
    {
        $resource = $this->createResource([
            'name' => '-?some()cr4zy=?"file\\-name/9&&',
        ]);
        $query = json_encode([
            'filter' => [[
                'type' => 'id',
                'values' => ['someid'],
            ]],
        ]);
        $searchQuery =
            (new SearchQueryBuilder())
            ->filter(new IdFilter(['someid']))
            ->build();
        $searchResult = new SearchResult(1, 1, 1, [$resource], [], null, 1);
        $this->serializer
            ->expects(once())
            ->method('deserialize')
            ->with($query, SearchQuery::class, 'json')
            ->willReturn($searchQuery);
        $this->search
            ->expects(once())
            ->method('search')
            ->with($searchQuery)
            ->willReturn($searchResult);
        $this->iCalFactory
            ->expects(once())
            ->method('createCalendarFromResourcesAsString')
            ->with([$resource])
            ->willReturn('Totally valid calendar data');
        $response = $this->controller->iCalBySearch(
            new Request(['query' => $query, 'occurrence' => '21.03.2025 12:00']),
        );
        $this->assertEquals(
            200,
            $response->getStatusCode(),
        );
        $this->assertEquals(
            'text/calendar',
            $response->headers->get('Content-Type'),
        );
        $this->assertEquals(
            'attachment; filename="-?some()cr4zy=?\"file-name9&&.ics"',
            $response->headers->get('Content-Disposition'),
        );
        $this->assertEquals(
            'Totally valid calendar data',
            $response->getContent(),
        );
    }

    public function testICalBySearchWithMissingQueryString(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->controller->iCalBySearch(new Request());
    }

    public function testICalBySearchWithInvalidOccurrenceDate(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $query = json_encode(['text' => 'valid_query']);
        $this->controller->iCalBySearch(new Request(['query' => $query, 'occurrence' => 'inv4l1d_da7e']));
    }

    public function testICalBySearchWithInvalidQueryString(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $query = 'invalid_query';
        $this->serializer
            ->expects(once())
            ->method('deserialize')
            ->with($query, SearchQuery::class, 'json')
            ->willThrowException(new NotNormalizableValueException());
        $this->controller->iCalBySearch(new Request(['query' => $query]));
    }

    public function testICalBySearchWithInvalidSearchQuery(): void
    {
        $this->expectException(HttpException::class);
        $query = json_encode([
            'filter' => [[
                'type' => 'id',
                'values' => ['someid'],
            ]],
        ]);
        $searchQuery =
            (new SearchQueryBuilder())
            ->filter(new IdFilter(['someid']))
            ->build();
        $this->serializer
            ->expects(once())
            ->method('deserialize')
            ->with($query, SearchQuery::class, 'json')
            ->willReturn($searchQuery);
        $this->search
            ->expects(once())
            ->method('search')
            ->with($searchQuery)
            ->willThrowException(new Exception());
        $this->controller->iCalBySearch(new Request(['query' => $query]));
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
            $data['lang'] ?? ResourceLanguage::default(),
            new DataBag($data),
        );
    }

    private function createResourceChannel(array $args): ResourceChannel
    {
        /** @var ResourceTenant $tenant */
        $tenant = $this->createStub(ResourceTenant::class);
        return new ResourceChannel(
            id: $args['id'] ?? '',
            name: $args['name'] ?? '',
            anchor: $args['anchor'] ?? '',
            serverName: $args['serverName'] ?? '',
            isPreview: $args['isPreview'] ?? false,
            nature: $args['nature'] ?? '',
            locale: $args['locale'] ?? '',
            baseDir: $args['baseDir'] ?? '',
            resourceDir: $args['resourceDir'] ?? '',
            configDir: $args['configDir'] ?? '',
            searchIndex: $args['searchIndex'] ?? '',
            translationLocales: $args['translationLocales'] ?? [],
            attributes: new DataBag([]),
            tenant: $tenant,
        );
    }
}
