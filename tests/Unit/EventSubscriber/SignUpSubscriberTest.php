<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\User;
use App\Event\SignUpEvent;
use App\EventSubscriber\SignUpSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignUpSubscriberTest extends TestCase
{
    private TemplatedEmail&MockObject $templatedEmail;
    private MailerInterface&MockObject $mailer;
    private TranslatorInterface&MockObject $translator;
    private Address $from;
    private SignUpSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->templatedEmail = $this->createMock(TemplatedEmail::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->from = new Address('sender@example.com', 'Sender');

        $this->subscriber = new SignUpSubscriber(
            $this->templatedEmail,
            $this->mailer,
            $this->translator,
            $this->from
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = SignUpSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(SignUpEvent::class, $events);
        $this->assertEquals('onSignUpEvent', $events[SignUpEvent::class]);
    }

    public function testOnSignUpEventSuccess(): void
    {
        $event = $this->createMock(SignUpEvent::class);
        $user = $this->createMock(User::class);

        $user->method('getEmail')->willReturn('newuser@example.com');
        $user->method('getFullName')->willReturn('Jane Doe');

        $event->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('Sign up')
            ->willReturn('Sign up');

        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with($this->callback(fn (Address $address) => 'newuser@example.com' === $address->getAddress() && 'Jane Doe' === $address->getName()))
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('from')
            ->with($this->from)
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('subject')
            ->with('Sign up')
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('htmlTemplate')
            ->with('email/confirm-email.html.twig')
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('context')
            ->with([
                'user' => $user,
            ])
            ->willReturnSelf();

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->templatedEmail);

        $this->subscriber->onSignUpEvent($event);
    }
}
