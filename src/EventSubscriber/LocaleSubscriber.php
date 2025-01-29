<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class LocaleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = 
            $request->query->get('_locale') 
            ?? $request->getSession()->get('locale')
            ?? $this->detectUserLocale();

        $request->getSession()->set('locale', $locale);
        $request->setLocale($locale);
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

        return $_SERVER['DEFAULT_LOCALE'];
    }
}
