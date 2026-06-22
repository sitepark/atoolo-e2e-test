<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Controller;

use Atoolo\EventsCalendar\Service\ICal\ICalFactory;
use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Search\Dto\Search\Query\SearchQuery;
use Atoolo\Search\Search;
use JsonException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ICalController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ResourceChannel $channel,
        private readonly ResourceLoader $loader,
        private readonly ICalFactory $iCalFactory,
        private readonly Search $search,
        private readonly SerializerInterface $serializer,
    ) {}

    /**
     * @throws JsonException
     */
    #[Route(
        '/api/ical/resource/{location}',
        name: 'atoolo_events_calendar_ical_location',
        methods: ['GET'],
        requirements: ['location' => '.+'],
        format: 'json',
        priority: 1,
    )]
    public function iCalByLocation(string $location, Request $request): Response
    {
        $resourceLocation = $this->toResourceLocation('', $location);
        $atOccurrence = $this->optParseOccurrence($request);
        return $this->createICalResponseByLocation($resourceLocation, $atOccurrence);
    }

    /**
     * @throws JsonException
     */
    #[Route(
        '/api/ical/resource/{lang}/{location}',
        name: 'atoolo_events_calendar_ical_lang_location',
        methods: ['GET'],
        requirements: ['location' => '.+'],
        format: 'json',
        priority: 2,
    )]
    public function iCalByLangAndLocation(string $lang, string $location, Request $request): Response
    {
        $resourceLocation = $this->toResourceLocation($lang, $location);
        $atOccurrence = $this->optParseOccurrence($request);
        return $this->createICalResponseByLocation($resourceLocation, $atOccurrence);
    }

    /**
     * @throws JsonException
     */
    #[Route(
        '/api/ical/search',
        name: 'atoolo_events_calendar_ical_search',
        methods: ['GET'],
        format: 'json',
    )]
    public function iCalBySearch(Request $request): Response
    {
        $query = $request->query->getString('query');
        if (empty($query)) {
            throw new BadRequestHttpException('query parameter \'query\' is empty');
        }
        $atOccurrence = $this->optParseOccurrence($request);
        $searchQuery = $this->deserializeSearchQuery($query);
        return $this->createICalResponseBySearchQuery($searchQuery, $atOccurrence);
    }

    private function optParseOccurrence(Request $request): ?\DateTime
    {
        $occurrenceRaw = $request->query->getString('occurrence');
        if (!empty($occurrenceRaw)) {
            try {
                return new \DateTime($occurrenceRaw);
            } catch (\Throwable $th) {
                throw new BadRequestHttpException(
                    'optional parameter \'occurrence\' could not be parsed to a DateTime object',
                    $th,
                );
            }
        }
        return null;
    }

    private function createICalResponseBySearchQuery(SearchQuery $searchQuery, ?\DateTime $atOccurrence = null): Response
    {
        try {
            $searchResult = $this->search->search($searchQuery);
        } catch (\Throwable $th) {
            $this->logger?->warning(
                'Something went wrong while exectuing the search query',
                [
                    'searchQuery' => $searchQuery,
                    'exception' => $th,
                ],
            );
            throw new HttpException(500, 'Something went wrong while processing the search query', $th);
        }
        return $this->createICalResponeByResources($searchResult->results, $atOccurrence);
    }

    private function deserializeSearchQuery(string $query): SearchQuery
    {
        try {
            return $this->serializer->deserialize(
                $query,
                SearchQuery::class,
                'json',
            );
        } catch (\Throwable $th) {
            $this->logger?->warning(
                'Something went wrong while trying to deserialize a search query.',
                [
                    'serializer' => $this->serializer,
                    'query' => $query,
                    'exception' => $th,
                ],
            );
            throw new BadRequestHttpException('Invalid query \'' . $query . '\'', $th);
        }
    }

    private function createICalResponseByLocation(ResourceLocation $location, ?\DateTime $atOccurrence = null): Response
    {
        try {
            $resource = $this->loader->load($location);
        } catch (ResourceNotFoundException $e) {
            throw new NotFoundHttpException('Resource at \'' . $location . '\' not found', $e);
        } catch (InvalidResourceException $e) {
            throw new HttpException(500, 'Resource at \'' . $location . '\' is invalid', $e);
        }
        return $this->createICalResponeByResources([$resource], $atOccurrence);
    }

    /**
     * @param Resource[] $resources
     */
    private function createICalResponeByResources(array $resources, ?\DateTime $atOccurrence = null): Response
    {
        $res = new Response($this->iCalFactory->createCalendarFromResourcesAsString($resources, $atOccurrence));
        $filename = 'ical';
        $filenameFallback = $filename;
        $suffix = '.ics';
        if (count($resources) === 1) {
            [$filename, $filenameFallback] = $this->toFilenameAndFilenameFallback($resources[0]->name);
        }
        $res->headers->set('Content-Type', 'text/calendar');
        $res->headers->set(
            'Content-Disposition',
            $res->headers->makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $filename . $suffix,
                $filenameFallback . $suffix,
            ),
        );
        ;
        return $res;
    }

    /**
     * Returns a filename and a filename fallback as expected by
     * `\Symfony\Component\HttpFoundation\HeaderUtils::makeDisposition`
     *
     * @return array{string, string}
     */
    private function toFilenameAndFilenameFallback(string $original): array
    {
        // strip path separators
        $filename = preg_replace('/[\\\\\/]+/', '', $original) ?? '';

        // strip path separators and %, replace chars to ascii in filename fallback
        $filenameCleanup = preg_replace('/[\\\\\/%]+/', '', $original) ?? '';
        $filenameFallback = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $filenameCleanup);

        return [$filename, $filenameFallback !== false ? $filenameFallback : $filenameCleanup];
    }

    private function toResourceLocation(string $lang, string $path): ResourceLocation
    {
        $suffix = str_ends_with($path, '.php') ? '' : '.php';
        if ($this->isSupportedTranslation($lang)) {
            return ResourceLocation::of('/' . $path . $suffix, ResourceLanguage::of($lang));
        }

        if (str_starts_with($this->channel->locale, $lang . '_')) {
            return ResourceLocation::of('/' . $path . $suffix);
        }

        // lang is not a language but part of the path, if not empty
        $location = (
            empty($lang)
            ? '/' . $path
            : '/' . $lang . '/' . $path
        ) . $suffix;

        return ResourceLocation::of($location);
    }

    private function isSupportedTranslation(string $lang): bool
    {
        foreach ($this->channel->translationLocales as $locale) {
            if (str_starts_with($locale, $lang . '_')) {
                return true;
            }
        }
        return false;
    }
}
