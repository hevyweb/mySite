<?php

namespace App\EventSubscriber;

use App\Event\ResetPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ResetPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TemplatedEmail $templatedEmail,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private Address $from,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResetPasswordEvent::class => 'resetPassword',
        ];
    }

    public function resetPassword(ResetPasswordEvent $event): void
    {
        $user = $event->getUser();

        $email = $this->templatedEmail
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->from($this->from)
            ->subject($this->translator->trans('New password', [], 'user'))
            ->htmlTemplate('email/reset-password.html.twig')
            ->context([
                'user' => $user,
                'password' => $event->getPassword(),
            ]);

        $this->mailer->send($email);
    }
}
