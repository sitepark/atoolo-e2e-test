<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Processor;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Processor\SpamDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(SpamDetector::class)]
class SpamDetectorTest extends TestCase
{
    private FormSubmission $submission;

    private SpamDetector $processor;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->processor = new SpamDetector();
        $this->submission = new FormSubmission(
            '127.0.0.1',
            $this->createStub(FormDefinition::class),
            new stdClass(),
        );
    }

    /**
     * @throws Exception
     */
    public function testProcessWithApproved(): void
    {
        $this->submission->approved = true;
        $this->expectNotToPerformAssertions();
        $this->processor->process($this->submission, []);
    }

    /**
     * @throws Exception
     */
    public function testProcess(): void
    {
        $this->expectNotToPerformAssertions();
        $this->processor->process($this->submission, []);
    }
}
