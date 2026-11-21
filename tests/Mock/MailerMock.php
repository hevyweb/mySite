<?php

namespace App\Tests\Mock;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class MailerMock implements MailerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        $this->logger->debug('Sending email');
    }
}
