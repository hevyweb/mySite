<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\User;
use App\Event\RecoverPasswordEvent;
use App\EventSubscriber\RecoverPasswordSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecoverPasswordSubscriberTest extends TestCase
{
    private TemplatedEmail&MockObject $templatedEmail;
    private MailerInterface&MockObject $mailer;
    private TranslatorInterface&MockObject $translator;
    private Address $from;
    private RecoverPasswordSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->templatedEmail = $this->createMock(TemplatedEmail::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->from = new Address('sender@example.com', 'Sender');

        $this->subscriber = new RecoverPasswordSubscriber(
            $this->templatedEmail,
            $this->mailer,
            $this->translator,
            $this->from
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = RecoverPasswordSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(RecoverPasswordEvent::class, $events);
        $this->assertEquals('recoverPassword', $events[RecoverPasswordEvent::class]);
    }

    public function testRecoverPasswordSuccess(): void
    {
        $event = $this->createMock(RecoverPasswordEvent::class);
        $user = $this->createMock(User::class);

        $user->method('getEmail')->willReturn('user@example.com');
        $user->method('getFullName')->willReturn('John Doe');

        $event->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('Recover password request', [], 'user')
            ->willReturn('Recover password request');

        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with($this->callback(fn (Address $address) => 'user@example.com' === $address->getAddress() && 'John Doe' === $address->getName()))
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('from')
            ->with($this->from)
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('subject')
            ->with('Recover password request')
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('htmlTemplate')
            ->with('email/recover-password.html.twig')
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('context')
            ->with($this->callback(function (array $context) use ($user) {
                return $context['user'] === $user && isset($context['timezone']);
            }))
            ->willReturnSelf();

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->templatedEmail);

        $this->subscriber->recoverPassword($event);
    }
}
