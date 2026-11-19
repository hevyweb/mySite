<?php

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\LocaleSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriberTest extends TestCase
{
    private LoggerInterface $logger;
    private LocaleSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subscriber = new LocaleSubscriber(
            $this->logger,
            'en',
            ['en', 'uk', 'pl']
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = LocaleSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertEquals([['onKernelRequest', 20]], $events[KernelEvents::REQUEST]);
    }

    public function testOnKernelRequestWithQueryLocale(): void
    {
        $request = Request::create('/?_locale=uk');
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->never())->method('get');
        $session->expects($this->once())->method('set')->with('user_locale', 'uk');
        $request->setSession($session);

        $event = $this->createMock(RequestEvent::class);
        $event->method('getRequest')->willReturn($request);

        $this->subscriber->onKernelRequest($event);

        $this->assertEquals('uk', $request->getLocale());
    }

    public function testOnKernelRequestWithSessionLocale(): void
    {
        $request = Request::create('/');
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('get')->with('user_locale')->willReturn('pl');
        $session->expects($this->once())->method('set')->with('user_locale', 'pl');
        $request->setSession($session);

        $event = $this->createMock(RequestEvent::class);
        $event->method('getRequest')->willReturn($request);

        $this->subscriber->onKernelRequest($event);

        $this->assertEquals('pl', $request->getLocale());
    }

    public function testOnKernelRequestWithUnsupportedLocaleFallsBackToDefault(): void
    {
        $request = Request::create('/?_locale=fr');
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn(null);
        $session->expects($this->once())->method('set')->with('user_locale', 'en');
        $request->setSession($session);

        $event = $this->createMock(RequestEvent::class);
        $event->method('getRequest')->willReturn($request);

        // Clear HTTP_ACCEPT_LANGUAGE to avoid detection interference
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);

        $this->subscriber->onKernelRequest($event);

        $this->assertEquals('en', $request->getLocale());
    }

    public function testOnKernelRequestHandlesSessionNotFoundExceptionAndDetectsLocale(): void
    {
        $request = Request::create('/');

        // Mock session that throws SessionNotFoundException when accessed
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willThrowException(new SessionNotFoundException('No session'));
        $request->setSession($session);

        $event = $this->createMock(RequestEvent::class);
        $event->method('getRequest')->willReturn($request);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('Session not found'));

        // Simulate browser language preference matching allowed detection ('uk' maps to 'uk')
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'uk-UA,uk;q=0.9';

        $this->subscriber->onKernelRequest($event);

        // Since detectUserLocale finds 'uk', and 'uk' is in the allowed locales array, it should be used.
        $this->assertEquals('uk', $request->getLocale());

        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }
}
