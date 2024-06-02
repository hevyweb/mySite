<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = $request->query->get('_locale');
        if (empty($locale)) {
            $locale = $this->detectUserLocale();
        }
        if (!empty($locale)) {
            $request->getSession()->set('locale', $locale);
        }
        if ($request->getSession()->has('locale')) {
            $event->getRequest()->setLocale($request->getSession()->get('locale'));
        }
    }

    private function detectUserLocale(): ?string
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $parts = explode(';', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            $userLocales = explode(',', $parts[0]);
            foreach ($userLocales as $locale) {
                if ('uk' == $locale || 'ru' == $locale) {
                    return 'ua';
                }
            }
        }

        return null;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
