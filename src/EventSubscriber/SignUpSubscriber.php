<?php

namespace App\EventSubscriber;

use App\Event\SignUpEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class SignUpSubscriber implements EventSubscriberInterface
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
            SignUpEvent::class => 'onSignUpEvent',
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function onSignUpEvent(SignUpEvent $event): void
    {
        $user = $event->getUser();

        $email = $this->templatedEmail
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->from($this->from)
            ->subject($this->translator->trans('Sign up'))
            ->htmlTemplate('email/confirm-email.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }
}
