<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\User;
use App\Event\ResetPasswordEvent;
use App\EventSubscriber\ResetPasswordSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordSubscriberTest extends TestCase
{
    private TemplatedEmail&MockObject $templatedEmail;
    private MailerInterface&MockObject $mailer;
    private TranslatorInterface&MockObject $translator;
    private Address $from;
    private ResetPasswordSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->templatedEmail = $this->createMock(TemplatedEmail::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->from = new Address('sender@example.com', 'Sender');

        $this->subscriber = new ResetPasswordSubscriber(
            $this->templatedEmail,
            $this->mailer,
            $this->translator,
            $this->from
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = ResetPasswordSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(ResetPasswordEvent::class, $events);
        $this->assertEquals('resetPassword', $events[ResetPasswordEvent::class]);
    }

    public function testResetPasswordSuccess(): void
    {
        $event = $this->createMock(ResetPasswordEvent::class);
        $user = $this->createMock(User::class);

        $user->method('getEmail')->willReturn('user@example.com');
        $user->method('getFullName')->willReturn('John Doe');

        $event->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $event->expects($this->once())
            ->method('getPassword')
            ->willReturn('secure-plain-password-123');

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('New password', [], 'user')
            ->willReturn('New password');

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
            ->with('New password')
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('htmlTemplate')
            ->with('email/reset-password.html.twig')
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('context')
            ->with([
                'user' => $user,
                'password' => 'secure-plain-password-123',
            ])
            ->willReturnSelf();

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->templatedEmail);

        $this->subscriber->resetPassword($event);
    }
}
