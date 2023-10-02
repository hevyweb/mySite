<?php

namespace App\EventSubscriber;

use App\Event\RecoverPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecoverPasswordSubscriber implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    private TemplatedEmail $templatedEmail;

    private TranslatorInterface $translator;

    private Address $from;

    public function __construct(
        TemplatedEmail      $templatedEmail,
        MailerInterface     $mailer,
        TranslatorInterface $translator,
        Address             $from
    )
    {
        $this->templatedEmail = $templatedEmail;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->from = $from;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RecoverPasswordEvent::class => 'recoverPassword'
        ];
    }

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