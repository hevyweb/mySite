<?php

namespace App\EventSubscriber;

use App\Event\NewEmailConfirmEvent;
use App\Event\OldEmailConfirmEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class OldEmailConfirmSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TemplatedEmail $templatedEmail,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private Address $from
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OldEmailConfirmEvent::class => 'confirmOldEmail',
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function confirmOldEmail(NewEmailConfirmEvent $event): void
    {
        $emailHistory = $event->getEmailHistory();
        if (empty($emailHistory->getOldEmailConfirmAt())) {
            $email = $this->templatedEmail
                ->to(new Address($emailHistory->getOldEmail(), $emailHistory->getUser()->getFullName()))
                ->from($this->from)
                ->subject($this->translator->trans('Confirm old email', [], 'user'))
                ->htmlTemplate('email/confirm-old-email.html.twig')
                ->context([
                    'token' => $emailHistory->getOldConfirmationToken(),
                    'user' => $emailHistory->getUser(),
                    'newEmail' => $emailHistory->getNewEmail(),
                ]);

            $this->mailer->send($email);
        }
    }
}
