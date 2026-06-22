<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Dto\FormData\UploadFile;
use Atoolo\Form\Exception\DataUrlException;
use Atoolo\Form\Service\DataUrlParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataUrlParser::class)]
class DataUrlParserTest extends TestCase
{
    private DataUrlParser $parser;

    public function setUp(): void
    {
        $this->parser = new DataUrlParser();
    }

    public function testParseWithInvalidUrl(): void
    {
        $this->expectException(DataUrlException::class);
        $this->parser->parse('x');
    }

    public function testParse(): void
    {
        $base64Data = base64_encode('text');
        $dataUrl = 'data:text/plain;name=text.txt;base64,' . $base64Data;

        $expected = new UploadFile(
            filename: 'text.txt',
            contentType: 'text/plain',
            data: 'text',
            size: 4,
        );
        $this->assertEquals(
            $expected,
            $this->parser->parse($dataUrl),
            'unexpected value',
        );
    }

    public function testParseWithInvalidBase64Data(): void
    {
        $invalidBase64 = "#";
        $dataUrl = 'data:text/plain;name=text.txt;base64,' . $invalidBase64;

        $this->expectException(DataUrlException::class);
        $this->parser->parse($dataUrl);
    }
}
