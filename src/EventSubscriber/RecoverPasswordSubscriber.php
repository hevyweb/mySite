<?php

namespace App\EventSubscriber;

use App\Event\RecoverPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class RecoverPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TemplatedEmail $templatedEmail,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private Address $from,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            RecoverPasswordEvent::class => 'recoverPassword',
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function recoverPassword(RecoverPasswordEvent $event): void
    {
        $user = $event->getUser();

        $email = $this->templatedEmail
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->from($this->from)
            ->subject($this->translator->trans('Recover password request', [], 'user'))
            ->htmlTemplate('email/recover-password.html.twig')
            ->context([
                'user' => $user,
                'timezone' => date_default_timezone_get(),
            ]);

        $this->mailer->send($email);
    }
}
