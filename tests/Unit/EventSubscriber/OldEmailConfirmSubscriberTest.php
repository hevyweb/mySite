<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\EmailHistory;
use App\Entity\User;
use App\Event\OldEmailConfirmEvent;
use App\EventSubscriber\OldEmailConfirmSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class OldEmailConfirmSubscriberTest extends TestCase
{
    private TemplatedEmail&MockObject $templatedEmail;
    private MailerInterface&MockObject $mailer;
    private TranslatorInterface&MockObject $translator;
    private Address $from;
    private OldEmailConfirmSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->templatedEmail = $this->createMock(TemplatedEmail::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->from = new Address('sender@example.com', 'Sender');

        $this->subscriber = new OldEmailConfirmSubscriber(
            $this->templatedEmail,
            $this->mailer,
            $this->translator,
            $this->from
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = OldEmailConfirmSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(OldEmailConfirmEvent::class, $events);
        $this->assertEquals('confirmOldEmail', $events[OldEmailConfirmEvent::class]);
    }

    public function testConfirmOldEmailAlreadyConfirmed(): void
    {
        $event = $this->createMock(OldEmailConfirmEvent::class);

        $emailHistory = $this->createMock(EmailHistory::class);
        $emailHistory->method('getOldEmailConfirmAt')->willReturn(new \DateTimeImmutable());

        $event->expects($this->once())
            ->method('getEmailHistory')
            ->willReturn($emailHistory);

        $this->mailer->expects($this->never())->method('send');

        $this->subscriber->confirmOldEmail($event);
    }

    public function testConfirmOldEmailSuccess(): void
    {
        $event = $this->createMock(OldEmailConfirmEvent::class);

        $emailHistory = $this->createMock(EmailHistory::class);
        $user = $this->createMock(User::class);

        $emailHistory->method('getOldEmailConfirmAt')->willReturn(null);
        $emailHistory->method('getOldEmail')->willReturn('olduser@example.com');
        $emailHistory->method('getOldConfirmationToken')->willReturn('old-token-123');
        $emailHistory->method('getNewEmail')->willReturn('newuser@example.com');
        $emailHistory->method('getUser')->willReturn($user);

        $user->method('getFullName')->willReturn('John Doe');

        $event->expects($this->once())
            ->method('getEmailHistory')
            ->willReturn($emailHistory);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('Confirm old email', [], 'user')
            ->willReturn('Confirm old email');

        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with($this->callback(fn (Address $address) => 'olduser@example.com' === $address->getAddress() && 'John Doe' === $address->getName()))
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())->method('from')->with($this->from)->willReturnSelf();
        $this->templatedEmail->expects($this->once())->method('subject')->with('Confirm old email')->willReturnSelf();
        $this->templatedEmail->expects($this->once())->method('htmlTemplate')->with('email/confirm-old-email.html.twig')->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('context')
            ->with([
                'token' => 'old-token-123',
                'user' => $user,
                'newEmail' => 'newuser@example.com',
            ])
            ->willReturnSelf();

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->templatedEmail);

        $this->subscriber->confirmOldEmail($event);
    }
}
