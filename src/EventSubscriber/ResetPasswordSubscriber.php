<?php

namespace App\EventSubscriber;

use App\Event\ResetPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordSubscriber implements EventSubscriberInterface
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
            ResetPasswordEvent::class => 'resetPassword'
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