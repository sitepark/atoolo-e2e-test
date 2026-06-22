<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Processor;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Processor\IpAllower;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(IpAllower::class)]
class IpAllowerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testProcess(): void
    {
        $processor = new IpAllower(['12.34.56.78']);
        $submission = new FormSubmission(
            '12.34.56.78',
            $this->createStub(FormDefinition::class),
            new stdClass(),
        );

        $resultSubmission = $processor->process($submission, []);
        $this->assertTrue($resultSubmission->approved, 'should be approved');
    }
}
