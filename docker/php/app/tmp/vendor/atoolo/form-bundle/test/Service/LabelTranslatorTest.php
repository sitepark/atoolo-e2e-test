<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Service\LabelTranslator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(LabelTranslator::class)]
class LabelTranslatorTest extends TestCase
{
    private TranslatorInterface $translator;
    private LabelTranslator $labelTranslator;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->translator = $this->createStub(
            TranslatorInterface::class,
        );
        $this->labelTranslator = new LabelTranslator($this->translator);
    }

    public function testTranslateLabelWithNull(): void
    {
        $translated = $this->labelTranslator->translateLabel(null);
        $this->assertNull(
            $translated,
            'unexpected value',
        );
    }

    public function testTranslateLabelWithoutPlaceholder(): void
    {
        $translated = $this->labelTranslator->translateLabel('test');
        $this->assertEquals(
            'test',
            $translated,
            'unexpected value',
        );
    }

    public function testTranslateLabelWithPlaceholder(): void
    {
        $this->translator->method('trans')
            ->willReturn('translated');
        $translated = $this->labelTranslator->translateLabel('${test}');

        $this->assertEquals(
            'translated',
            $translated,
            'unexpected value',
        );
    }

    public function testTranslateFieldsOfArray(): void
    {

        $this->translator->method('trans')
            ->willReturn('translated');

        $fields = ['field'];
        $data = ['field' => '${key}'];
        $translated = $this->labelTranslator->translate($data, $fields);

        $this->assertEquals(
            ['field' => 'translated'],
            $translated,
            'unexpected value',
        );
    }

    public function testTranslateFieldsOfArrayRecursive(): void
    {

        $this->translator->method('trans')
            ->willReturn('translated');

        $fields = ['field'];
        $data = ['object' => [ 'field' => '${key}' ]];
        $translated = $this->labelTranslator->translate($data, $fields);

        $this->assertEquals(
            ['object' => [ 'field' => 'translated' ]],
            $translated,
            'unexpected value',
        );
    }

}
