<?php

namespace App\Tests\Application\Controller;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends AbstractApplicationTestCase
{
    private EntityManagerInterface $entityManager;
    protected array $uploadedFiles = [];

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $imagesArticleDir = $this->getContainer()->getParameter('images_article');
        $fixtureImage = 'test_image.jpg';
        $fullFixturePath = $imagesArticleDir . '/' . $fixtureImage;
        $sourceTestImagePath = __DIR__ . '/Resource/test_test.jpg';

        if (!is_dir($imagesArticleDir)) {
            mkdir($imagesArticleDir, 0777, true);
        }

        if (!file_exists($fullFixturePath) && file_exists($sourceTestImagePath)) {
            copy($sourceTestImagePath, $fullFixturePath);
            $this->uploadedFiles[] = $fullFixturePath;
        }
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->uploadedFiles as $file) {
            if (file_exists($file)) {
                @chmod($file, 0666);
                @unlink($file);
            }
        }
    }

    public function testArticleListIndex(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('article-list'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Articles');
        $this->assertSelectorExists('table');
    }

    public function testArticleListAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('article-list'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testArticleListRedirectsForAnonymous(): void
    {
        $this->client->request('GET', $this->router->generate('article-list'));
        $this->assertResponseRedirects($this->router->generate('user-login'));
    }

    public function testArticleCreateFormDisplay(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('article-create'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Create an article');
        $this->assertSelectorExists('form[name="form"]');
    }

    public function testArticleCreateSuccess(): void
    {
        $this->logInAdmin();
        $crawler = $this->client->request('GET', $this->router->generate('article-create'));

        $form = $crawler->selectButton('Save')->form();

        $imagePath = __DIR__ . '/Resource/test_test.jpg';
        /** @var FileFormField $image */
        $image = $form['form[translation][image]'];
        $image->upload($imagePath);

        /** @var ChoiceFormField $checkbox */
        $checkbox = $form['form[translation][draft]'];
        $checkbox->untick();

        $form->setValues([
            'form[translation][title]' => 'New Article Title',
            'form[article][slug]' => 'new-article-slug',
            'form[translation][body]' => '<b>New article body</b>. Some text.',
            'form[translation][locale]' => 'en',
            'form[article][tags]' => 'new-tag, another-tag',
            'form[translation][preview]' => 'New article preview.',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects($this->router->generate('article-list'));

        $this->entityManager->clear();
        /** @var Article $article */
        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug('new-article-slug');
        $this->assertNotNull($article);
        $this->assertEquals('New Article Title', $article->getArticleTranslation('en')->getTitle());
        $this->assertEquals('new-article-slug', $article->getSlug());
        $this->assertCount(2, $article->getTags());
    }

    public function testArticleUpdateFormDisplay(): void
    {
        $this->logInAdmin();
        /** @var Article $article */
        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug('test_slug');
        $translation = $article->getArticleTranslation('en');

        $this->client->request('GET', $this->router->generate('article-edit', [
            'slug' => $article->getSlug(),
            'locale' => $translation->getLocale(),
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Update an article');
        $this->assertSelectorExists('form[name="form"]');
        $this->assertInputValueSame('form[translation][title]', $translation->getTitle());
    }

    public function testArticleUpdateSuccess(): void
    {
        $this->logInAdmin();
        /** @var Article $article */
        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug('test_slug');
        $originalSlug = $article->getSlug();
        $originalTranslation = $article->getArticleTranslation('en');

        $crawler = $this->client->request('GET', $this->router->generate('article-edit', [
            'slug' => $originalSlug,
            'locale' => $originalTranslation->getLocale(),
        ]));
        $form = $crawler->selectButton('Update')->form();

        $imagePath = __DIR__ . '/Resource/test_test.jpg';
        /** @var FileFormField $image */
        $image = $form['form[translation][image]'];
        $image->upload($imagePath);

        /** @var ChoiceFormField $checkbox */
        $checkbox = $form['form[translation][draft]'];
        $checkbox->tick();

        $form->setValues([
            'form[translation][title]' => 'Updated Article Title',
            'form[article][slug]' => 'updated-test-slug',
            'form[translation][body]' => 'Updated article body content.',
            'form[translation][locale]' => 'en',
            'form[article][tags]' => 'updated-tag',
            'form[translation][preview]' => 'Updated article preview.',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects($this->router->generate('article-list'));

        $this->entityManager->clear();
        /** @var Article $updatedArticle */
        $updatedArticle = $this->entityManager->getRepository(Article::class)->findOneBySlug('updated-test-slug');
        $this->assertNotNull($updatedArticle);
        $this->assertEquals('Updated Article Title', $updatedArticle->getArticleTranslation('en')->getTitle());
        $this->assertEquals('updated-test-slug', $updatedArticle->getSlug());
        $this->assertTrue($updatedArticle->getArticleTranslation('en')->isDraft());
        $this->assertCount(1, $updatedArticle->getTags());
    }

    public function testArticleUpdateNotFound(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('article-edit', [
            'slug' => 'non-existent-slug',
            'locale' => 'en',
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testArticleUpdateLocaleFallback(): void
    {
        $this->logInAdmin();
        /** @var Article $article */
        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug('test_slug');
        
        $this->client->request('GET', $this->router->generate('article-edit', [
            'slug' => $article->getSlug(),
            'locale' => 'uk', 
        ]));
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Update an article');
    }

    public function testArticleUpdateNoTranslation(): void
    {
        $this->logInAdmin();
        
        // This article has no translations in fixtures
        $this->client->request('GET', $this->router->generate('article-edit', [
            'slug' => 'no_translation_slug',
            'locale' => 'en',
        ]));

        // Based on controller logic: if (!$translation) { ... return $this->redirectToRoute('article-create'); }
        $this->assertResponseRedirects($this->router->generate('article-create'));
    }

    public function testArticleTranslate(): void
    {
        $this->logInAdmin();
        /** @var ArticleTranslation $originalTranslation */
        $originalTranslation = $this->entityManager->getRepository(ArticleTranslation::class)->findOneBy(['title' => 'Translation title', 'locale' => 'en']);
        $originalArticleId = $originalTranslation->getArticle()->getId();
        $originalImageFilename = $originalTranslation->getImage();

        $this->client->request('GET', $this->router->generate('article-translate', [
            'id' => $originalTranslation->getId(),
            'locale' => 'uk'
        ]));

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        
        $this->entityManager->clear();
        /** @var Article $article */
        $article = $this->entityManager->getRepository(Article::class)->find($originalArticleId);
        $newTranslation = $article->getArticleTranslation('uk');

        $this->assertNotNull($newTranslation, 'The translation for uk locale should have been created.');
        $this->assertNotNull($newTranslation->getImage(), 'The image for the new translation should have been cloned.');
        $this->assertNotEquals($originalImageFilename, $newTranslation->getImage());
    }

    public function testArticleTranslateAlreadyExists(): void
    {
        $this->logInAdmin();
        /** @var ArticleTranslation $translation */
        $translation = $this->entityManager->getRepository(ArticleTranslation::class)->findOneBy(['locale' => 'en']);
        
        $this->client->request('GET', $this->router->generate('article-translate', [
            'id' => $translation->getId(),
            'locale' => 'en'
        ]));

        $this->assertResponseRedirects($this->router->generate('article-edit', [
            'slug' => $translation->getArticle()->getSlug(),
            'locale' => 'en'
        ]));
    }

    public function testArticleTranslateTranslationNotFound(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('article-translate', [
            'id' => 99999,
            'locale' => 'uk'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testArticleTranslateLocaleNotFound(): void
    {
        $this->logInAdmin();
        /** @var ArticleTranslation $translation */
        $translation = $this->entityManager->getRepository(ArticleTranslation::class)->findOneBy(['locale' => 'en']);
        
        $this->client->request('GET', "/administrator/article/translate/{$translation->getId()}/fr");
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testArticleFormAutosave(): void
    {
        $this->logInAdmin();
        /** @var ArticleTranslation $translation */
        $translation = $this->entityManager->getRepository(ArticleTranslation::class)->findOneBy(['locale' => 'en']);

        $this->client->request('POST', $this->router->generate('article-form-update', ['id' => $translation->getId()]), [
            'form' => [
                'translation' => [
                    'title' => 'Autosaved Title',
                    'body' => 'Autosaved body content.',
                    'locale' => 'en',
                    'preview' => 'Autosaved preview.',
                ],
                'article' => [
                    'slug' => 'autosaved-slug',
                    'tags' => 'tag1',
                ]
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Autosaved success.', $responseData['message']);

        $this->entityManager->clear();
        $updatedTranslation = $this->entityManager->getRepository(ArticleTranslation::class)->find($translation->getId());
        $this->assertEquals('Autosaved Title', $updatedTranslation->getTitle());
        $this->assertTrue($updatedTranslation->isDraft());
    }

    public function testArticleFormAutosaveTranslationNotFound(): void
    {
        $this->logInAdmin();
        $this->client->request('POST', $this->router->generate('article-form-update', ['id' => 99999]), [
            'form' => [
                'translation' => [
                    'title' => 'Autosaved Title',
                    'body' => 'Autosaved body content.',
                    'locale' => 'en',
                    'preview' => 'Autosaved preview.',
                ],
                'article' => [
                    'slug' => 'autosaved-slug',
                    'tags' => 'tag1',
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Autosave failed because of invalid id.', $responseData['error']);
    }

    public function testArticleFormAutosaveNoId(): void
    {
        $this->logInAdmin();
        $this->client->request('POST', $this->router->generate('article-form-update'), [
            'form' => [
                'translation' => [
                    'title' => '',
                    'body' => '',
                    'locale' => 'en',
                    'preview' => '',
                ],
                'article' => [
                    'slug' => '',
                    'tags' => '',
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testArticleDeleteSingle(): void
    {
        $this->logInAdmin();
        /** @var Article $articleToDelete */
        $articleToDelete = $this->entityManager->getRepository(Article::class)->findOneBySlug('deletable-article-1');
        $articleId = $articleToDelete->getId();

        $this->client->request('POST', $this->router->generate('article-delete'), ['id' => [$articleId => 'on']]);

        $this->assertResponseRedirects($this->router->generate('article-list'));

        $this->entityManager->clear();
        $deletedArticle = $this->entityManager->getRepository(Article::class)->find($articleId);
        $this->assertNull($deletedArticle);
    }

    public function testArticleDeleteMultiple(): void
    {
        $this->logInAdmin();
        /** @var Article $article1 */
        $article1 = $this->entityManager->getRepository(Article::class)->findOneBySlug('deletable-article-1');
        /** @var Article $article2 */
        $article2 = $this->entityManager->getRepository(Article::class)->findOneBySlug('deletable-article-2');

        $id1 = $article1 ? $article1->getId() : null;
        $id2 = $article2 ? $article2->getId() : null;

        $this->client->request('POST', $this->router->generate('article-delete'), ['id' => [$id1 => 'on', $id2 => 'on']]);

        $this->assertResponseRedirects($this->router->generate('article-list'));

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->getRepository(Article::class)->find($id1));
        $this->assertNull($this->entityManager->getRepository(Article::class)->find($id2));
    }

    public function testArticleDeleteNotFound(): void
    {
        $this->logInAdmin();
        $this->client->request('POST', $this->router->generate('article-delete'), ['id' => [99999 => 'on']]);
        $this->assertResponseRedirects($this->router->generate('article-list'));
    }

    public function testArticleCreateAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('article-create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testArticleUpdateAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        /** @var Article $article */
        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug('test_slug');
        $translation = $article->getArticleTranslation('en');

        $this->client->request('GET', $this->router->generate('article-edit', [
            'slug' => $article->getSlug(),
            'locale' => $translation->getLocale(),
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testArticleDeleteAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        $this->client->request('POST', $this->router->generate('article-delete'), ['id' => [1 => 'on']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
