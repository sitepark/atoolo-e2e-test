<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Processor;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Exception\AccessDeniedException;
use Atoolo\Form\Processor\IpBlocker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(IpBlocker::class)]
class IpBlockerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testProcessWithApproved(): void
    {
        $processor = new IpBlocker(['12.34.56.78']);
        $submission = new FormSubmission(
            '12.34.56.78',
            $this->createStub(FormDefinition::class),
            new stdClass(),
        );

        $submission->approved = true;
        $resultSubmission = $processor->process($submission, []);
        $this->assertTrue($resultSubmission->approved, 'should be approved');
    }

    /**
     * @throws Exception
     */
    public function testProcessWithBlockedIp(): void
    {
        $processor = new IpBlocker(['12.34.56.78']);
        $submission = new FormSubmission(
            '12.34.56.78',
            $this->createStub(FormDefinition::class),
            new stdClass(),
        );

        $this->expectException(AccessDeniedException::class);
        $processor->process($submission, []);
    }

    /**
     * @throws Exception
     */
    public function testProcessNonBlockedIp(): void
    {
        $processor = new IpBlocker(['12.34.56.78']);
        $submission = new FormSubmission(
            '127.0.0.1',
            $this->createStub(FormDefinition::class),
            new stdClass(),
        );

        $this->expectNotToPerformAssertions();
        $processor->process($submission, []);
    }

}
