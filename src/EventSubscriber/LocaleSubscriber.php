<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @param array<string> $locales
     */
    public function __construct(
        private LoggerInterface $logger,
        private string $defaultLocale,
        private array $locales,
    ) {
    }

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
        $locale = $this->getLocale($request);
        if (!in_array($locale, $this->locales)) {
            $locale = $this->defaultLocale;
        }
        $request->getSession()->set('user_locale', $locale);
        $request->setLocale($locale);
    }

    private function getLocale(Request $request): string
    {
        try {
            $locale = $request->query->get('_locale') ??
                $request->getSession()
                    ->get('user_locale');
            if (!is_null($locale)) {
                return $locale;
            }
        } catch (SessionNotFoundException $e) {
            $this->logger->debug('Session not found: '.$e->getMessage());
        }

        return $this->detectUserLocale() ?? $this->defaultLocale;
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
}
