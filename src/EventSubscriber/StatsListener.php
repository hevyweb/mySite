<?php

namespace App\EventSubscriber;

use App\Service\StatsTracker;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::TERMINATE)]
readonly class StatsListener
{
    public function __construct(private StatsTracker $statsTracker)
    {
    }

    public function __invoke(KernelEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->statsTracker->track($event->getRequest());
    }
}
