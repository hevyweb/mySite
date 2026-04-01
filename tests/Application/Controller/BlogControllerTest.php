<?php

namespace App\Tests\Application\Controller;

use Symfony\Component\BrowserKit\Cookie;

class BlogControllerTest extends AbstractApplicationTestCase
{
    public function testBlogIndex(): void
    {
        $this->client->request('GET', $this->router->generate('blog-list'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Blog');
    }

    public function testBlogIndexPagination(): void
    {
        $this->client->request('GET', $this->router->generate('blog-list', ['page' => 2]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Blog');
    }

    public function testBlogIndexInvalidPage(): void
    {
        $this->client->request('GET', '/blog/0');
        $this->assertResponseStatusCodeSame(404);

        $this->client->request('GET', '/blog/invalid');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testBlogView(): void
    {
        $this->client->request('GET', $this->router->generate('blog-view', ['slug' => 'test_slug']));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Translation title');
    }

    public function testBlogViewNotFound(): void
    {
        $this->client->request('GET', $this->router->generate('blog-view', ['slug' => 'non-existent-slug']));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testBlogViewHitIncrement(): void
    {
        $this->client->request('GET', $this->router->generate('blog-view', ['slug' => 'test_slug']));
        $this->assertResponseIsSuccessful();
        $this->assertResponseHasCookie('hit');
    }

    public function testBlogViewHitWithExistingCookie(): void
    {
        $cookie = new Cookie('hit', '999,1000');
        $this->client->getCookieJar()->set($cookie);

        $this->client->request('GET', $this->router->generate('blog-view', ['slug' => 'test_slug']));
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHasCookie('hit');
        
        $responseCookie = $this->client->getCookieJar()->get('hit');
        $this->assertStringContainsString('999', $responseCookie->getValue());
        $this->assertStringContainsString('1000', $responseCookie->getValue());
    }
}
