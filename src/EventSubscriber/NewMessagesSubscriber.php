<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\NewMessagesEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewMessagesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TemplatedEmail      $templatedEmail,
        private MailerInterface     $mailer,
        private TranslatorInterface $translator,
        private Address             $from,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewMessagesEvent::class => 'sendNotification'
        ];
    }

    public function sendNotification(NewMessagesEvent $event): void
    {
        /**
         * @var User[] $adminUsers
         */
        $adminUsers = $this->entityManager->getRepository(User::class)->getAdmins();
        if (count($adminUsers)) {
            $email = $this->templatedEmail
                ->from($this->from)
                ->subject($this->translator->trans('You have new messages.', [], 'contactme'))
                ->htmlTemplate('email/new-messages.html.twig')
                ->context([
                    'newMessages' => $event->getNewMessages(),
                ]);
            while (count($adminUsers)) {
                try {
                    $user = array_shift($adminUsers);
                    $email->to(new Address($user->getEmail(), $user->getFullName()));
                    break;
                } catch (RfcComplianceException $exception) {
                    $this->logger->warning('User with id "' . $user->getId() . '" has invalid email');
                }
            }

            if (count($adminUsers)) {
                foreach ($adminUsers as $user) {
                    try {
                        $email->addCc(new Address($user->getEmail(), $user->getFullName()));
                    } catch (RfcComplianceException $exception) {
                        $this->logger->warning('User with id "' . $user->getId() . '" has invalid email');
                    }
                }
            }

            $this->mailer->send($email);
        }
    }
}