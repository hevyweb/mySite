<?php

namespace App\Tests\Application\Controller;

class ArticleControllerTest extends AbstractApplicationTestCase
{
    public function testTranslate()
    {
        $url = $this->rout->generate('article-translate');
    }

    public function testUpdate()
    {
        $url = $this->rout->generate('article-edit');
    }

    public function testIndex()
    {
        $url = $this->rout->generate('article-list');
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    public function testDelete()
    {
        $url = $this->rout->generate('article-delete');
    }

    public function testCreate()
    {
        $url = $this->rout->generate('article-create');
    }
}
