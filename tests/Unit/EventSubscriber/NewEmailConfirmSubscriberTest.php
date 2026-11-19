<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\EmailHistory;
use App\Entity\User;
use App\Event\NewEmailConfirmEvent;
use App\EventSubscriber\NewEmailConfirmSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewEmailConfirmSubscriberTest extends TestCase
{
    private TemplatedEmail $templatedEmail;
    private MailerInterface $mailer;
    private TranslatorInterface $translator;
    private Address $from;
    private NewEmailConfirmSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->templatedEmail = $this->createMock(TemplatedEmail::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->from = new Address('sender@example.com', 'Sender');

        $this->subscriber = new NewEmailConfirmSubscriber(
            $this->templatedEmail,
            $this->mailer,
            $this->translator,
            $this->from
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = NewEmailConfirmSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(NewEmailConfirmEvent::class, $events);
        $this->assertEquals('confirmNewEmail', $events[NewEmailConfirmEvent::class]);
    }

    public function testConfirmNewEmailAlreadyConfirmed(): void
    {
        $event = $this->createMock(NewEmailConfirmEvent::class);

        $emailHistory = $this->createMock(EmailHistory::class);
        $emailHistory->method('getNewEmailConfirmAt')->willReturn(new \DateTimeImmutable());

        $event->expects($this->once())
            ->method('getEmailHistory')
            ->willReturn($emailHistory);

        $this->mailer->expects($this->never())->method('send');

        $this->subscriber->confirmNewEmail($event);
    }

    public function testConfirmNewEmailSuccess(): void
    {
        $event = $this->createMock(NewEmailConfirmEvent::class);

        $emailHistory = $this->createMock(EmailHistory::class);
        $user = $this->createMock(User::class);

        $emailHistory->method('getNewEmailConfirmAt')->willReturn(null);
        $emailHistory->method('getNewEmail')->willReturn('newuser@example.com');
        $emailHistory->method('getNewConfirmationToken')->willReturn('secret-token-123');
        $emailHistory->method('getUser')->willReturn($user);

        $user->method('getFullName')->willReturn('John Doe');

        $event->expects($this->once())
            ->method('getEmailHistory')
            ->willReturn($emailHistory);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('Confirm New Email', [], 'user')
            ->willReturn('Confirm New Email');

        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with($this->callback(fn (Address $address) => 'newuser@example.com' === $address->getAddress() && 'John Doe' === $address->getName()))
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())->method('from')->with($this->from)->willReturnSelf();
        $this->templatedEmail->expects($this->once())->method('subject')->with('Confirm New Email')->willReturnSelf();
        $this->templatedEmail->expects($this->once())->method('htmlTemplate')->with('email/confirm-new-email.html.twig')->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('context')
            ->with([
                'token' => 'secret-token-123',
                'user' => $user,
                'newEmail' => 'newuser@example.com',
            ])
            ->willReturnSelf();

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->templatedEmail);

        $this->subscriber->confirmNewEmail($event);
    }
}
