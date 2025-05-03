<?php

namespace App\Tests\Application\Controller;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FileFormField;

class ArticleControllerTest extends AbstractApplicationTestCase
{
    public function testCreate(): void
    {
        $this->logInAdmin();
        $crawler = $this->client->request('GET', $this->router->generate('article-create'));

        $form = $crawler->selectButton('Save')->form();

        /**
         * @var FileFormField $image
         */
        $image = $form['form[translation][image]'];
        $image->upload(__DIR__.'/Resource/test.jpg');
        /**
         * @var ChoiceFormField $checkbox
         */
        $checkbox = $form['form[translation][draft]'];
        $checkbox->untick();

        $form->setValues([
            'form[translation][title]' => 'Dummy title',
            'form[article][slug]' => 'Dummy slug',
            'form[translation][body]' => '<b>Bold text</b> <p>paragraph</p>. Text',
            'form[translation][locale]' => 'en',
            'form[article][tags]' => 'tag1, tag2, tag3',
            'form[translation][preview]' => 'Preview text',
        ]);
        $this->client->submit($form);
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
        $this->assertEquals(['tag1', 'tag2', 'tag3'], $article->getTags()->map(fn (Tag $tag) => $tag->getName())->toArray());
    }

    public function testUpdate(): void
    {
        $this->logInAdmin();
        $crawler = $this->client->request('GET', $this->router->generate('article-edit',
            [
                'slug' => 'test_slug',
                'locale' => 'en',
            ]));
        $form = $crawler->selectButton('Update')->form();
        /**
         * @var FileFormField $image
         */
        $image = $form['form[translation][image]'];
        $image->upload(__DIR__.'/Resource/test.jpg');

        /**
         * @var ChoiceFormField $checkbox
         */
        $checkbox = $form['form[translation][draft]'];
        $checkbox->untick();

        $form->setValues([
            'form[translation][title]' => 'Dummy title',
            'form[article][slug]' => 'Dummy slug',
            'form[translation][body]' => '<b>Bold text</b> <p>paragraph</p>. Text',
            'form[translation][locale]' => 'ua',
            'form[article][tags]' => 'tag1, tag2, tag3',
            'form[translation][preview]' => 'Preview text',
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        /**
         * @var Article $article
         */
        $article = $entityManager->getRepository(Article::class)->findOneBySlug('Dummy slug');
        $translation = $article->getArticleTranslation('ua');
        $this->assertInstanceOf(ArticleTranslation::class, $translation);
        $this->assertSame('Dummy title', $translation->getTitle());
        $this->assertSame('<b>Bold text</b> <p>paragraph</p>. Text', $translation->getBody());
        $this->assertSame('Preview text', $translation->getPreview());

        $this->assertEquals(['tag1', 'tag2', 'tag3'], $article->getTags()->map(fn (Tag $tag) => $tag->getName())->toArray());
    }
}
