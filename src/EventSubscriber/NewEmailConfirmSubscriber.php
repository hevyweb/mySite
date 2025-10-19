<?php

namespace App\EventSubscriber;

use App\Event\NewEmailConfirmEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class NewEmailConfirmSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TemplatedEmail $templatedEmail,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private Address $from,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function confirmNewEmail(NewEmailConfirmEvent $event): void
    {
        $emailHistory = $event->getEmailHistory();
        if (empty($emailHistory->getNewEmailConfirmAt())) {
            $email = $this->templatedEmail
                ->to(new Address($emailHistory->getNewEmail(), $emailHistory->getUser()->getFullName()))
                ->from($this->from)
                ->subject($this->translator->trans('Confirm New Email', [], 'user'))
                ->htmlTemplate('email/confirm-new-email.html.twig')
                ->context([
                    'token' => $emailHistory->getNewConfirmationToken(),
                    'user' => $emailHistory->getUser(),
                    'newEmail' => $emailHistory->getNewEmail(),
                ]);

            $this->mailer->send($email);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewEmailConfirmEvent::class => 'confirmNewEmail',
        ];
    }
}
