<?php

namespace App\EventSubscriber;

use App\Event\SignUpEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SignUpSubscriber implements EventSubscriberInterface
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
            SignUpEvent::class => 'onSignUpEvent',
        ];
    }

    public function onSignUpEvent(Event $event): void
    {
        $user = $event->getUser();

        $email = $this->templatedEmail
            ->to(new Address($user->getEmail(), $user->getFullName()()))
            ->from($this->from)
            ->subject($this->translator->trans('Sign up'))
            ->htmlTemplate('email/confirm-email.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }
}
