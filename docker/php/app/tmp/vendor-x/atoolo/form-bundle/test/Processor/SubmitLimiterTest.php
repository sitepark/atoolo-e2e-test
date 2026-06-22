<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Processor;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Exception\LimitExceededException;
use Atoolo\Form\Processor\IpLimiter;
use Atoolo\Form\Processor\SubmitLimiter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

#[CoversClass(SubmitLimiter::class)]
class SubmitLimiterTest extends TestCase
{
    private SubmitLimiter $processor;

    private FormSubmission $submission;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $formSubmitByIpLimiter = new RateLimiterFactory([
            'id' => 'formSubmitByIp',
            'policy' => 'fixed_window',
            'limit' => 1,
            'interval' => '1 minute',
        ], new InMemoryStorage());
        $this->processor = new SubmitLimiter($formSubmitByIpLimiter);

        $this->submission = new FormSubmission(
            '127.0.0.1',
            $this->createStub(FormDefinition::class),
            new stdClass(),
        );
    }

    public function testProcess(): void
    {
        $this->expectNotToPerformAssertions();
        $this->processor->process($this->submission, []);
    }

    public function testProcessLimit(): void
    {
        $this->processor->process($this->submission, []);

        $this->expectException(LimitExceededException::class);
        $this->processor->process($this->submission, []);
    }

    public function testProcessWishApprove(): void
    {
        $this->submission->approved = true;

        $this->expectNotToPerformAssertions();
        $this->processor->process($this->submission, []);
        $this->processor->process($this->submission, []);
    }
}
