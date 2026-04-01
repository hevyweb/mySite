<?php

namespace App\Tests\Application\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;

class PageControllerTest extends AbstractApplicationTestCase
{
    public function testPageList(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('page-list'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Page content');
    }

    public function testPageCreate(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('page-create'));
        $this->assertResponseIsSuccessful();
        
        $this->client->submitForm('Save', [
            'page[title]' => 'Test Page Title',
            'page[body]' => 'Test page content.',
            'page[locale]' => 'en',
            'page[route]' => 'home',
        ]);

        $this->assertResponseRedirects($this->router->generate('page-list'));
        
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $page = $em->getRepository(Page::class)->findOneBy(['title' => 'Test Page Title']);
        $this->assertNotNull($page);
    }

    public function testPageUpdate(): void
    {
        $this->logInAdmin();
        
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['title' => 'Initial Title']);
        $pageId = $page->getId();

        $this->client->request('GET', $this->router->generate('page-update', ['id' => $pageId]));
        $this->assertResponseIsSuccessful();
        
        $this->client->submitForm('Save', [
            'page[title]' => 'Updated Page Title',
        ]);

        $this->assertResponseRedirects($this->router->generate('page-list'));
        
        $em->clear();
        $updatedPage = $em->getRepository(Page::class)->find($pageId);
        $this->assertEquals('Updated Page Title', $updatedPage->getTitle());
    }
}
