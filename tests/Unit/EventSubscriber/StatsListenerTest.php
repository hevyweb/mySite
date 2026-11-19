<?php

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\StatsListener;
use App\Service\StatsTracker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StatsListenerTest extends TestCase
{
    private StatsTracker $statsTracker;
    private HttpKernelInterface $kernel;
    private StatsListener $listener;

    protected function setUp(): void
    {
        $this->statsTracker = $this->createMock(StatsTracker::class);
        $this->kernel = $this->createMock(HttpKernelInterface::class);
        $this->listener = new StatsListener($this->statsTracker);
    }

    public function testInvokeWithSubRequestDoesNotTrack(): void
    {
        $request = Request::create('/');
        $event = new KernelEvent($this->kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $this->statsTracker->expects($this->never())
            ->method('track');

        ($this->listener)($event);
    }

    public function testInvokeWithMainRequestTracksStats(): void
    {
        $request = Request::create('/');
        $response = new \Symfony\Component\HttpFoundation\Response();

        $event = new TerminateEvent($this->kernel, $request, $response);

        $this->statsTracker->expects($this->once())
            ->method('track')
            ->with($request);

        ($this->listener)($event);
    }
}
