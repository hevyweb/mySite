<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\User;
use App\Event\NewMessagesEvent;
use App\EventSubscriber\NewMessagesSubscriber;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewMessagesSubscriberTest extends TestCase
{
    private TemplatedEmail&MockObject $templatedEmail;
    private MailerInterface&MockObject $mailer;
    private TranslatorInterface&MockObject $translator;
    private Address $from;
    private EntityManagerInterface&MockObject $entityManager;
    private UserRepository&MockObject $userRepository;
    private LoggerInterface&MockObject $logger;
    private NewMessagesSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->templatedEmail = $this->createMock(TemplatedEmail::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->from = new Address('sender@example.com', 'Sender');
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->entityManager->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);

        $this->subscriber = new NewMessagesSubscriber(
            $this->templatedEmail,
            $this->mailer,
            $this->translator,
            $this->from,
            $this->entityManager,
            $this->logger
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = NewMessagesSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(NewMessagesEvent::class, $events);
        $this->assertEquals('sendNotification', $events[NewMessagesEvent::class]);
    }

    public function testSendNotificationNoAdmins(): void
    {
        $event = $this->createMock(NewMessagesEvent::class);

        $this->userRepository->expects($this->once())
            ->method('getAdmins')
            ->willReturn([]);

        $this->mailer->expects($this->never())->method('send');

        $this->subscriber->sendNotification($event);
    }

    public function testSendNotificationSuccessWithToAndCc(): void
    {
        $event = $this->createMock(NewMessagesEvent::class);
        $event->method('getNewMessages')->willReturn(5);

        $admin1 = $this->createMock(User::class);
        $admin1->method('getId')->willReturn(1);
        $admin1->method('getEmail')->willReturn('admin1@example.com');
        $admin1->method('getFullName')->willReturn('Admin One');

        $admin2 = $this->createMock(User::class);
        $admin2->method('getId')->willReturn(2);
        $admin2->method('getEmail')->willReturn('admin2@example.com');
        $admin2->method('getFullName')->willReturn('Admin Two');

        $this->userRepository->expects($this->once())
            ->method('getAdmins')
            ->willReturn([$admin1, $admin2]);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('You have new messages.', [], 'contactme')
            ->willReturn('You have new messages.');

        $this->templatedEmail->expects($this->once())->method('from')->with($this->from)->willReturnSelf();
        $this->templatedEmail->expects($this->once())->method('subject')->with('You have new messages.')->willReturnSelf();
        $this->templatedEmail->expects($this->once())->method('htmlTemplate')->with('email/new-messages.html.twig')->willReturnSelf();
        $this->templatedEmail->expects($this->once())->method('context')->with(['newMessages' => 5])->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with($this->callback(fn (Address $address) => 'admin1@example.com' === $address->getAddress()))
            ->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('addCc')
            ->with($this->callback(fn (Address $address) => 'admin2@example.com' === $address->getAddress()))
            ->willReturnSelf();

        $this->mailer->expects($this->once())->method('send')->with($this->templatedEmail);

        $this->subscriber->sendNotification($event);
    }

    public function testSendNotificationHandlesRfcComplianceException(): void
    {
        $event = $this->createMock(NewMessagesEvent::class);
        $event->method('getNewMessages')->willReturn(0);

        $validAdmin = $this->createMock(User::class);
        $validAdmin->method('getId')->willReturn(2);
        $validAdmin->method('getEmail')->willReturn('valid@example.com');
        $validAdmin->method('getFullName')->willReturn('Valid Admin');

        $this->userRepository->expects($this->once())
            ->method('getAdmins')
            ->willReturn([$validAdmin]);

        $this->translator->method('trans')->willReturn('You have new messages.');

        $this->templatedEmail->method('from')->willReturnSelf();
        $this->templatedEmail->method('subject')->willReturnSelf();
        $this->templatedEmail->method('htmlTemplate')->willReturnSelf();
        $this->templatedEmail->method('context')->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with($this->callback(fn (Address $address) => 'valid@example.com' === $address->getAddress()))
            ->willReturnSelf();

        $this->mailer->expects($this->once())->method('send');

        $this->subscriber->sendNotification($event);
    }

    public function testSendNotificationInvalidEmailInToLoop(): void
    {
        $event = $this->createMock(NewMessagesEvent::class);
        $event->method('getNewMessages')->willReturn(2);

        $invalidAdmin = $this->createMock(User::class);
        $invalidAdmin->method('getId')->willReturn(1);
        $invalidAdmin->method('getEmail')->willReturn('invalid-email-address');
        $invalidAdmin->method('getFullName')->willReturn('Invalid Admin');

        $validAdmin = $this->createMock(User::class);
        $validAdmin->method('getId')->willReturn(2);
        $validAdmin->method('getEmail')->willReturn('valid@example.com');
        $validAdmin->method('getFullName')->willReturn('Valid Admin');

        $this->userRepository->expects($this->once())
            ->method('getAdmins')
            ->willReturn([$invalidAdmin, $validAdmin]);

        $this->translator->method('trans')->willReturn('You have new messages.');

        $this->templatedEmail->method('from')->willReturnSelf();
        $this->templatedEmail->method('subject')->willReturnSelf();
        $this->templatedEmail->method('htmlTemplate')->willReturnSelf();
        $this->templatedEmail->method('context')->willReturnSelf();

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('User with id "1" has invalid email'));

        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with($this->callback(fn (Address $address) => 'valid@example.com' === $address->getAddress()))
            ->willReturnSelf();

        $this->mailer->expects($this->once())->method('send');

        $this->subscriber->sendNotification($event);
    }

    public function testSendNotificationInvalidEmailInCcLoop(): void
    {
        $event = $this->createMock(NewMessagesEvent::class);
        $event->method('getNewMessages')->willReturn(1);

        $admin1 = $this->createMock(User::class);
        $admin1->method('getId')->willReturn(1);
        $admin1->method('getEmail')->willReturn('admin1@example.com');
        $admin1->method('getFullName')->willReturn('Admin One');

        $invalidAdmin = $this->createMock(User::class);
        $invalidAdmin->method('getId')->willReturn(2);
        $invalidAdmin->method('getEmail')->willReturn('invalid-email-address');
        $invalidAdmin->method('getFullName')->willReturn('Invalid CC Admin');

        $this->userRepository->expects($this->once())
            ->method('getAdmins')
            ->willReturn([$admin1, $invalidAdmin]);

        $this->translator->method('trans')->willReturn('You have new messages.');

        $this->templatedEmail->method('from')->willReturnSelf();
        $this->templatedEmail->method('subject')->willReturnSelf();
        $this->templatedEmail->method('htmlTemplate')->willReturnSelf();
        $this->templatedEmail->method('context')->willReturnSelf();

        $this->templatedEmail->expects($this->once())
            ->method('to')
            ->with($this->callback(fn (Address $address) => 'admin1@example.com' === $address->getAddress()))
            ->willReturnSelf();

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('User with id "2" has invalid email'));

        $this->templatedEmail->expects($this->never())->method('addCc');

        $this->mailer->expects($this->once())->method('send');

        $this->subscriber->sendNotification($event);
    }
}
