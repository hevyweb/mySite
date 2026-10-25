<?php

namespace App\EventSubscriber;

use App\Service\StatsTracker;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::TERMINATE)]
readonly class StatsListener
{
    public function __construct(private StatsTracker $statsTracker) {}

    public function __invoke(TerminateEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->statsTracker->track($event->getRequest());
    }
}