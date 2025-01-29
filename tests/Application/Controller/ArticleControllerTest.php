<?php

namespace App\Tests\Application\Controller;

class ArticleControllerTeeest extends AbstractApplicationTestCase
{
    public function testTranslate(): void
    {
        $url = $this->rout->generate('article-translate');
    }

    public function testUpdate(): void
    {
        $url = $this->rout->generate('article-edit');
    }

    public function testIndex(): void
    {
        $url = $this->rout->generate('article-list');
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    public function testDelete(): void
    {
        $url = $this->rout->generate('article-delete');
    }

    public function testCreate(): void
    {
        $url = $this->rout->generate('article-create');
    }
}
