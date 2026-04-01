<?php

namespace App\Tests\Application\Controller;

class ContactMeControllerTest extends AbstractApplicationTestCase
{
    public function testContactMeIndex(): void
    {
        $this->client->request('GET', $this->router->generate('contact-me'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Contact me');
        $this->assertSelectorExists('form[name="contact_me"]');
    }

    public function testContactMeFormSubmission(): void
    {
        $this->client->request('GET', $this->router->generate('contact-me'));
        
        $this->client->submitForm('Send', [
            'contact_me[name]' => 'Test User',
            'contact_me[email]' => 'test@example.com',
            'contact_me[subject]' => 'Test Subject',
            'contact_me[message]' => 'This is a test message.',
            'contact_me[recaptcha]' => 'mock-token',
        ]);

        $this->assertResponseRedirects($this->router->generate('home'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.toast-body', 'Thanks for the message.');
    }

    public function testContactMeInvalidCsrfToken(): void
    {
        // Submit the form directly without first requesting the page to simulate a missing or invalid CSRF token
        $this->client->request('POST', $this->router->generate('contact-me'), [
            'contact_me' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => 'Test Subject',
                'message' => 'This is a test message.',
                'recaptcha' => 'mock-token',
                '_token' => 'invalid-token', // Provide an invalid token
            ],
        ]);

        $this->assertResponseRedirects($this->router->generate('contact-me'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.toast-body', 'Some error happened. Please try again.');
    }
}
