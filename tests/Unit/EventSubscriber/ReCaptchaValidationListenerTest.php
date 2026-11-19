<?php

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\ReCaptchaValidationListener;
use PHPUnit\Framework\TestCase;
use ReCaptcha\ReCaptcha;
use ReCaptcha\Response;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class ReCaptchaValidationListenerTest extends TestCase
{
    private ReCaptcha $reCaptchaMock;
    private ReCaptchaValidationListener $listener;

    protected function setUp(): void
    {
        $this->reCaptchaMock = $this->createMock(ReCaptcha::class);
        $this->listener = new ReCaptchaValidationListener($this->reCaptchaMock);

        // Reset global request state for predictable test environments
        $_POST = [];
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    protected function tearDown(): void
    {
        $_POST = [];
        unset($_SERVER['HTTP_HOST'], $_SERVER['REMOTE_ADDR']);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = ReCaptchaValidationListener::getSubscribedEvents();
        $this->assertArrayHasKey(FormEvents::POST_SUBMIT, $events);
        $this->assertEquals('onPostSubmit', $events[FormEvents::POST_SUBMIT]);
    }

    public function testOnPostSubmitSuccess(): void
    {
        $_POST['g-recaptcha-response'] = 'valid-token';

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('isSuccess')
            ->willReturn(true);

        $this->reCaptchaMock->expects($this->once())
            ->method('setExpectedHostname')
            ->with('localhost')
            ->willReturnSelf();

        $this->reCaptchaMock->expects($this->once())
            ->method('verify')
            ->with('valid-token', '127.0.0.1')
            ->willReturn($responseMock);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('addError');

        $event = new FormEvent($form, null);

        $this->listener->onPostSubmit($event);
    }

    public function testOnPostSubmitFailureAddsFormError(): void
    {
        $_POST['g-recaptcha-response'] = 'invalid-token';

        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('isSuccess')
            ->willReturn(false);

        $this->reCaptchaMock->expects($this->once())
            ->method('setExpectedHostname')
            ->with('localhost')
            ->willReturnSelf();

        $this->reCaptchaMock->expects($this->once())
            ->method('verify')
            ->with('invalid-token', '127.0.0.1')
            ->willReturn($responseMock);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('addError')
            ->with($this->callback(fn (FormError $error) => 'The captcha is invalid. Please try again.' === $error->getMessage()));

        $event = new FormEvent($form, null);

        $this->listener->onPostSubmit($event);
    }
}
