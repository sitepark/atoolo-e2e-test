<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Test\Service;

use Atoolo\Deployment\Service\StopWorkerOnRedeployListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\Worker;

#[CoversClass(StopWorkerOnRedeployListener::class)]
class StopWorkerOnRedeployListenerTest extends TestCase
{
    private string $workDir = __DIR__
        . '/../../var/test/StopWorkerOnRedeployListener';

    private string $resourceDir = __DIR__ . '/..'
        . '/resources/Service/StopWorkerOnRedeployListenerTest';

    private string $projectDir1;

    private string $projectDir2;

    private string $symlink;

    private string $originScriptFilename;

    private StopWorkerOnRedeployListener $listener;
    private LoggerInterface $logger;

    public function setUp(): void
    {

        if (
            !is_dir($this->workDir)
            && mkdir($this->workDir, 0777, true) === false
        ) {
            throw new RuntimeException(
                'unable to create work directory ' . $this->workDir,
            );
        }

        $this->projectDir1 = $this->resourceDir . '/project-1';
        $this->projectDir2 = $this->resourceDir . '/project-2';
        $this->symlink = $this->workDir . '/project_link';
        $scriptFileName = $this->symlink . '/bin/console';

        @unlink($this->workDir . '/project_link');
        symlink($this->projectDir1, $this->symlink);

        $this->originScriptFilename = $_SERVER['SCRIPT_FILENAME'];
        $_SERVER['SCRIPT_FILENAME'] = $scriptFileName;

        $this->logger = $this->createStub(LoggerInterface::class);
        $this->listener = new StopWorkerOnRedeployListener(
            $this->logger,
        );
    }

    public function tearDown(): void
    {
        @unlink($this->symlink);
        $_SERVER['SCRIPT_FILENAME'] = $this->originScriptFilename;
    }

    public function testOnWorkerRunningIsNotIdle(): void
    {
        $worker = $this->createMock(Worker::class);
        $event = new WorkerRunningEvent($worker, false);

        $worker->expects($this->never())
            ->method('stop');
        $this->listener->onWorkerRunning($event);
    }

    public function testOnWorkerRunningIsIdleShouldNotStop(): void
    {
        $worker = $this->createMock(Worker::class);
        $event = new WorkerRunningEvent($worker, true);

        $this->listener->onWorkerStarted();

        $worker->expects($this->never())
            ->method('stop');
        $this->listener->onWorkerRunning($event);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [
                WorkerStartedEvent::class => 'onWorkerStarted',
                WorkerRunningEvent::class => 'onWorkerRunning',
            ],
            StopWorkerOnRedeployListener::getSubscribedEvents(),
            'should return the correct events',
        );
    }

    public function testSymlinkChanged(): void
    {
        $worker = $this->createMock(Worker::class);
        $worker->expects($this->once())
            ->method('stop');
        $event = new WorkerRunningEvent($worker, true);

        $this->listener->onWorkerRunning($event);
        unlink($this->symlink);
        symlink($this->projectDir2, $this->symlink);
        $this->listener->onWorkerRunning($event);
    }

    public function testMissingScriptFilenameServerVar(): void
    {
        unset($_SERVER['SCRIPT_FILENAME']);
        $this->expectException(RuntimeException::class);
        new StopWorkerOnRedeployListener(
            $this->createStub(LoggerInterface::class),
        );
    }

    public function testMissingInvalidScriptFilenameServerVar(): void
    {
        $_SERVER['SCRIPT_FILENAME'] = 'invalid';
        $this->expectException(RuntimeException::class);
        new StopWorkerOnRedeployListener(
            $this->createStub(LoggerInterface::class),
        );
    }

    public function testSymlinkChangedWithInvalidNewLink(): void
    {
        $worker = $this->createMock(Worker::class);
        $worker->expects($this->once())
            ->method('stop');
        $event = new WorkerRunningEvent($worker, true);

        unlink($this->symlink);
        $this->listener->onWorkerRunning($event);
    }
}
