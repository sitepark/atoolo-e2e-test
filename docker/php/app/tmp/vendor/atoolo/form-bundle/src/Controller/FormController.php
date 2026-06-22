<?php

declare(strict_types=1);

namespace Atoolo\Form\Controller;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Exception\FormNotFoundException;
use Atoolo\Form\Service\FormDefinitionLoader;
use Atoolo\Form\Service\SubmitHandler;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLocation;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\LocaleSwitcher;

class FormController extends AbstractController
{
    public Serializer $serializer;

    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $channel,
        private readonly FormDefinitionLoader $formDefinitionLoader,
        private readonly SubmitHandler $submitHandler,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
        $encoders = [new JsonEncoder()];
        $normalizers = [new BackedEnumNormalizer(), new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route("/api/form/{_locale}/{location}/{component}", name: "atoolo_form_definition", requirements: ['location' => '.+'], methods: ['GET'], format: 'json')]
    public function definition(string $_locale, string $location, string $component): Response
    {
        $lang = $_locale;
        $definition = $this->loadDefinition($lang, $location, $component);

        $json = $this->serializer->serialize($definition, 'json', [
            'json_encode_options' => JSON_THROW_ON_ERROR,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['processors'], // ignore-attribute not jet working
        ]);

        return new JsonResponse(data: $json, json: true);
    }

    #[Route("/api/form/{_locale}/{location}/{component}", name: "atoolo_form_submit", requirements: ['location' => '.+'], methods: ['POST'], format: 'json')]
    public function submit(
        string $_locale,
        string $location,
        string $component,
        Request $request,
    ): Response {

        $lang = $_locale;
        $data = $this->requestBodyToObject($request);

        $formDefinition = $this->loadDefinition($lang, $location, $component);

        $submission = new FormSubmission(
            $request->getClientIp() ?? '',
            $formDefinition,
            $data,
        );

        $this->submitHandler->handle($submission);

        return $this->json(['status' => 200]);
    }

    private function requestBodyToObject(Request $request): stdClass
    {
        if ($request->getContentTypeFormat() !== 'json') {
            throw new UnsupportedMediaTypeHttpException('Unsupported Media Type');
        }

        if ('' === $content = $request->getContent()) {
            throw new JsonException('Request body is empty.');
        }

        try {
            $data = json_decode($content, false, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
            if (!($data instanceof stdClass)) {
                throw new JsonException('Request body is not an object: ' . $content);
            }
            return $data;
        } catch (\JsonException $e) {
            throw new JsonException('Could not decode request body: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function loadDefinition(string $lang, string $path, string $component): FormDefinition
    {
        $location = $this->toResourceLocation($lang, $path);
        try {
            return $this->formDefinitionLoader->loadFromResource($location, $component);
        } catch (ResourceNotFoundException) {
            throw new NotFoundHttpException('Resource \'' . $location . '\' not found');
        } catch (FormNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    private function toResourceLocation(string $lang, string $path): ResourceLocation
    {
        if ($this->isSupportedTranslation($lang)) {
            $this->localeSwitcher->setLocale($lang);
            return ResourceLocation::of('/' . $path . '.php', ResourceLanguage::of($lang));
        }

        if (str_starts_with($this->channel->locale, $lang . '_')) {
            return ResourceLocation::of('/' . $path . '.php');
        }

        // lang is not a language but part of the path, if not empty
        $location = (
            empty($lang)
                ? '/' . $path
                : '/' . $lang . '/' . $path
        ) . '.php';

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
