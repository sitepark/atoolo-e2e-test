<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service;

use Atoolo\Runtime\Check\Service\CheckStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CheckStatus::class)]
class CheckStatusTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        $status = CheckStatus::createSuccess();
        self::assertTrue($status->success, 'Status is not successful');
    }

    public function testCreateFailure(): void
    {
        $status = CheckStatus::createFailure();
        self::assertFalse($status->success, 'Status is not a failure');
    }

    public function testAddMessage(): void
    {
        $status = new CheckStatus(true);
        $status->addMessage('scope', 'message');
        self::assertSame(
            [
                'scope' => ['message'],
            ],
            $status->getMessages(),
            'Message was not added correctly',
        );
    }

    public function testAddReport(): void
    {
        $status = new CheckStatus(true);
        $status->addReport('scope', ['result']);
        self::assertSame(
            [
                'scope' => ['result'],
            ],
            $status->getReports(),
            'Result was not added correctly',
        );
    }

    public function testReplaceReport(): void
    {
        $status = new CheckStatus(true);
        $status->addReport('scope', ['result']);
        $status->replaceReport('scope', ['result2']);
        self::assertSame(
            [
                'scope' => ['result2'],
            ],
            $status->getReports(),
            'Result was not replaced',
        );
    }

    public function testGetReport(): void
    {
        $status = new CheckStatus(true);
        $status->addReport('scope', ['result']);
        self::assertSame(
            ['result'],
            $status->getReport('scope'),
            'unexpected result',
        );
    }

    public function testGetMissingReport(): void
    {
        $status = new CheckStatus(true);
        self::assertSame(
            [],
            $status->getReport('scope'),
            'unexpected result',
        );
    }

    public function testAddResultWithScopeAlreadyExists(): void
    {
        $status = new CheckStatus(true);
        $status->addReport('scope', ['result']);

        $this->expectExceptionMessage('Scope scope already exists');
        $status->addReport('scope', ['result']);
    }

    public function testApply(): void
    {
        $status = new CheckStatus(true);
        $toApply = CheckStatus::createFailure();
        $toApply->addReport('scope', ['result']);
        $status->apply($toApply);

        $expected = new CheckStatus(false);
        $expected->addReport('scope', ['result']);
        self::assertEquals(
            $expected,
            $status,
            'Status was not applied correctly',
        );
    }

    public function testSerialize(): void
    {
        $status = new CheckStatus(true);
        $status->addMessage('scope', 'message');
        $status->addReport('scope', ['a' => 'b']);
        $serialized = $status->serialize();
        self::assertSame(
            [
                'success' => true,
                'reports' => [
                    'scope' => [
                        'a' => 'b',
                    ],
                ],
                'messages' => [
                    'scope' => ['message'],
                ],
            ],
            $serialized,
            'Status was not serialized correctly',
        );
    }

    public function testDeserialize(): void
    {
        $serialized = [
            'success' => true,
            'reports' => [
                'scope' => [
                    'a' => 'b',
                ],
            ],
            'messages' => [
                'scope' => ['message'],
            ],
        ];
        $status = CheckStatus::deserialize($serialized);
        $expected = new CheckStatus(true);
        $expected->addMessage('scope', 'message');
        $expected->addReport('scope', ['a' => 'b']);

        self::assertEquals(
            $expected,
            $status,
            'Status was not deserialized correctly',
        );
    }

    /**
     * @throws \JsonException
     */
    public function testWithUnknownJson(): void
    {
        $serialized = [
            'code' => 401,
            'message' => "JWT Token not found",
        ];
        $status = CheckStatus::deserialize($serialized);

        $expected = new CheckStatus(false);
        $expected->addMessage(
            'deserialize',
            json_encode($serialized, JSON_THROW_ON_ERROR),
        );

        self::assertEquals(
            $expected,
            $status,
            'Status was not deserialized correctly',
        );
    }
}
