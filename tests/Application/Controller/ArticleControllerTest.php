<?php

namespace App\Tests\Application\Controller;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

class ArticleControllerTest extends AbstractApplicationTestCase
{
    public function testCreate(): void
    {   
        $this->logInAdmin();
        $crawler = $this->client->request('GET', $this->router->generate('article-create'));

        $form = $crawler->selectButton('Save')->form();

        $form['form[translation][image]']->upload(__DIR__.'/Resource/test.jpg');
        $form['form[translation][draft]']->untick();

        $form->setValues([
            'form[translation][title]' => 'Dummy title',
            'form[article][slug]' => 'Dummy slug',
            'form[translation][body]' => '<b>Bold text</b> <p>paragraph</p>. Text',
            'form[translation][locale]' => 'en',
            'form[article][tags]' => 'tag1, tag2, tag3',
            'form[translation][preview]' => 'Preview text',
        ]);
        $this->client->submit($form);
        file_put_contents(__DIR__.'/test.html', $this->client->getResponse()->getContent());
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $article = $entityManager->getRepository(Article::class)->findOneBySlug('Dummy slug');
        if (null === $article) {
            $this->fail('Article not found');
        }
        /**
         * @var Article $article
         */
        $translation = $article->getArticleTranslation('en');
        $this->assertEquals('Dummy title', $translation->getTitle());
        $this->assertEquals('<b>Bold text</b> <p>paragraph</p>. Text', $translation->getBody());
        $this->assertEquals('Preview text', $translation->getPreview());
        $this->assertEquals(['tag1', 'tag2', 'tag3'], $article->getTags()->map(fn(Tag $tag) => $tag->getName())->toArray());
    }
    
    public function testUpdate(): void
    {
        $this->logInAdmin();
        $crawler = $this->client->request('GET', $this->router->generate('article-update', ['id' => 1]));
        $form = $crawler->selectButton('Save')->form();
        $form['form[translation][image]']->upload(__DIR__.'/Resource/test.jpg');
        $form['form[translation][draft]']->untick();
        $form->setValues([
            'form[translation][title]' => 'Dummy title',
            'form[article][slug]' => 'Dummy slug',
            'form[translation][body]' => '<b>Bold text</b> <p>paragraph</p>. Text',
            'form[translation][locale]' => 'ua',
            'form[article][tags]' => 'tag1, tag2, tag3',
            'form[article][preview]' => 'Preview text',
        ]);
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        
    }
}
