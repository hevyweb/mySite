<?php

namespace App\Tests\Application\Controller;

use App\Entity\Experience;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class ExperienceControllerTest extends AbstractApplicationTestCase
{
    protected EntityManagerInterface $em;
    
    protected RouterInterface $router;

    protected array $uploadedFiles = [];

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->router = $this->getContainer()->get('router');
        $this->logInAdmin();
    }

    public static function invalidExperienceDataProvider(): \Generator
    {
        yield 'blank name' => [
            ['experience[name]' => ''],
            'This value should not be blank.',
        ];

        yield 'name too short' => [
            ['experience[name]' => 'a'],
            'This value is too short. It should have 2 characters or more.',
        ];

        yield 'name too long' => [
            ['experience[name]' => str_repeat('a', 256)],
            'This value is too long. It should have 255 characters or less.',
        ];

        yield 'blank description' => [
            ['experience[description]' => ''],
            'This value should not be blank.',
        ];

        yield 'blank location' => [
            ['experience[location]' => ''],
            'This value should not be blank.',
        ];

        yield 'location too long' => [
            ['experience[location]' => str_repeat('a', 65)],
            'This value is too long. It should have 64 characters or less.',
        ];

        yield 'invalid from date' => [
            ['experience[fromDate]' => '99.32.2029'],
            'Please enter a valid date.',
        ];

        yield 'invalid to date' => [
            ['experience[toDate]' => 'invalid-date'],
            'Please enter a valid date.',
        ];

        yield 'company too long' => [
            ['experience[company]' => str_repeat('a', 65)],
            'This value is too long. It should have 64 characters or less.',
        ];
    }

    public function testExperienceListIndex(): void
    {
        $this->client->request('GET', $this->router->generate('experience-list'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table#experience-table');
        $this->assertSelectorTextContains('h2', 'Experiences');
        
        $crawler = $this->client->getCrawler();
        $rows = $crawler->filter('table#experience-table tbody tr');
        $this->assertCount(20, $rows);
    }

    public function testCreateExperienceFormDisplay(): void
    {
        $this->client->request('GET', $this->router->generate('experience-create'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Add working place');
        $this->assertSelectorExists('form[name="experience"]');
    }

    public function testCreateExperienceSuccess(): void
    {
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Senior Developer', 'locale' => 'en']);
        $this->assertNotNull($experience);
        $this->assertEquals('Senior Developer', $experience->getName());
        $this->assertEquals('Tech Company Inc', $experience->getCompany());
        $this->assertEquals('San Francisco, USA', $experience->getLocation());
    }

    public function testCreateExperienceWithoutImage(): void
    {
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Developer', 'locale' => 'en']);
        $this->assertNotNull($experience);
        $this->assertEquals('Developer', $experience->getName());
    }

    public function testCreateExperienceWithImage(): void
    {
        $imagePath = __DIR__ . '/Resource/test_test.jpg';
        $uploadedFile = new UploadedFile(
            $imagePath,
            'test_test.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request('GET', $this->router->generate('experience-create'));

        $this->client->submitForm('Create', [
            'experience[name]' => 'Position with Image',
            'experience[locale]' => 'en',
            'experience[description]' => 'Description for position with image.',
            'experience[location]' => 'Image Location',
            'experience[company]' => 'Image Company',
            'experience[fromDate]' => '2023-01-01',
            'experience[image]' => $uploadedFile,
        ]);

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $this->em->clear();
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Position with Image']);
        $this->assertNotNull($experience);
        $this->assertNotNull($experience->getImage());
    }

    public function testUpdateExperienceWithImage(): void
    {
        $imagePath = __DIR__ . '/Resource/test_test.jpg';
        $uploadedFile = new UploadedFile(
            $imagePath,
            'test.jpg',
            'image/jpeg',
            null,
            true
        );

        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Senior Developer', 'locale' => 'en']);
        $experienceId = $experience->getId();

        $this->client->request('GET', $this->router->generate('experience-update', ['id' => $experienceId]));

        $this->client->submitForm('Update', [
            'experience[name]' => 'Updated Project Manager',
            'experience[locale]' => 'en',
            'experience[description]' => 'Updated description.',
            'experience[location]' => 'Updated Location',
            'experience[company]' => 'Updated Company',
            'experience[fromDate]' => '2022-01-01',
            'experience[toDate]' => '2023-01-01',
            'experience[image]' => $uploadedFile,
        ]);

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $this->em->clear();
        $updatedExperience = $this->em->getRepository(Experience::class)->find($experienceId);
        $this->assertEquals('Updated Project Manager', $updatedExperience->getName());
        $this->assertNotNull($updatedExperience->getImage());
    }
    
    public function testDeleteExperienceWithImage(): void
    {
        // First create an experience with an image to ensure we have one to delete
        $imagePath = __DIR__ . '/Resource/test_test.jpg';
        $uploadedFile = new UploadedFile(
            $imagePath,
            'test.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request('GET', $this->router->generate('experience-create'));
        $this->client->submitForm('Create', [
            'experience[name]' => 'To Be Deleted',
            'experience[locale]' => 'en',
            'experience[description]' => 'This will be deleted.',
            'experience[location]' => 'Delete Location',
            'experience[company]' => 'Delete Company',
            'experience[fromDate]' => '2023-01-01',
            'experience[image]' => $uploadedFile,
        ]);

        $this->em->clear();
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'To Be Deleted']);
        $experienceId = $experience->getId();

        // Now delete it
        $this->client->request(
            'POST',
            $this->router->generate('experience-delete'),
            ['id' => [$experienceId => 'on']]
        );

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $this->em->clear();
        $deletedExperience = $this->em->getRepository(Experience::class)->find($experienceId);
        $this->assertNull($deletedExperience);
    }

    /**
     * @param array<string, string|null> $invalidData
     *
     * @dataProvider invalidExperienceDataProvider
     */
    public function testCreateExperienceValidationError(array $invalidData, string $errorMessage): void
    {
        $this->client->request('GET', $this->router->generate('experience-create'));

        $validData = [
            'experience[name]' => 'Test Position',
            'experience[locale]' => 'en',
            'experience[description]' => 'This is a valid test description for the position.',
            'experience[company]' => 'Test Company',
            'experience[location]' => 'Test Location',
            'experience[fromDate]' => '2020-01-01',
        ];

        $data = array_merge($validData, $invalidData);

        $this->client->submitForm('Create', $data);
        
        $crawler = $this->client->getCrawler();
        $errorMessages = $crawler->filter('.form_validation_error')->each(fn($node) => $node->text());
        $foundError = false;
        
        foreach ($errorMessages as $msg) {
            if (strpos($msg, $errorMessage) !== false) {
                $foundError = true;
                break;
            }
        }
        
        $this->assertTrue(
            $foundError,
            sprintf('Expected error message "%s" not found. Found errors: %s', $errorMessage, implode(', ', $errorMessages))
        );
    }

    public function testUpdateExperienceSuccess(): void
    {
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Lead Developer', 'locale' => 'en']);
        $experienceId = $experience->getId();

        $this->client->request('GET', $this->router->generate('experience-update', ['id' => $experienceId]));

        $this->client->submitForm('Update', [
            'experience[name]' => 'Updated Position',
            'experience[locale]' => 'en',
            'experience[description]' => 'Updated description with new responsibilities.',
            'experience[location]' => 'Updated Location',
            'experience[company]' => 'Updated Company',
            'experience[fromDate]' => '2021-01-01',
            'experience[toDate]' => '2022-01-01',
        ]);

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $this->em->clear();
        $updatedExperience = $this->em->getRepository(Experience::class)->find($experienceId);
        $this->assertEquals('Updated Position', $updatedExperience->getName());
        $this->assertEquals('Updated Company', $updatedExperience->getCompany());
    }

    /**
     * @param array<string, string|null> $invalidData
     *
     * @dataProvider invalidExperienceDataProvider
     */
    public function testUpdateExperienceValidationError(array $invalidData, string $errorMessage): void
    {
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'DevOps Engineer', 'locale' => 'en']);
        $experienceId = $experience->getId();

        $this->client->request('GET', $this->router->generate('experience-update', ['id' => $experienceId]));

        $validData = [
            'experience[name]' => 'Updated Position',
            'experience[locale]' => 'en',
            'experience[description]' => 'Updated valid description.',
            'experience[company]' => 'Updated Company',
            'experience[location]' => 'Updated Location',
            'experience[fromDate]' => '2021-01-01',
            'experience[toDate]' => '2022-01-01',
        ];

        $data = array_merge($validData, $invalidData);

        $this->client->submitForm('Update', $data);
        
        $this->assertResponseIsSuccessful();
        
        $crawler = $this->client->getCrawler();
        $errorMessages = $crawler->filter('.form_validation_error')->each(fn($node) => $node->text());
        $foundError = false;
        
        foreach ($errorMessages as $msg) {
            if (strpos($msg, $errorMessage) !== false) {
                $foundError = true;
                break;
            }
        }
        
        $this->assertTrue(
            $foundError,
            sprintf('Expected error message "%s" not found. Found errors: %s', $errorMessage, implode(', ', $errorMessages))
        );
    }

    public function testTreeViewExperiencesByLocale(): void
    {
        $this->client->restart();;
        $this->client->request('GET', $this->router->generate('experience-tree'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('My working experience', $this->client->getResponse()->getContent() ?? '');
    }

    public function testDeleteExperienceSuccess(): void
    {
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Database Administrator', 'locale' => 'en']);
        $experienceId = $experience->getId();

        $this->client->request(
            'POST',
            $this->router->generate('experience-delete'),
            ['id' => [$experienceId => 'on']]
        );

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $this->em->clear();
        $deletedExperience = $this->em->getRepository(Experience::class)->find($experienceId);
        $this->assertNull($deletedExperience);
    }

    public function testDeleteMultipleExperiencesSuccess(): void
    {
        $experience1 = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Frontend Developer', 'locale' => 'en']);
        $experience2 = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Backend Developer', 'locale' => 'en']);
        $id1 = $experience1->getId();
        $id2 = $experience2->getId();

        $this->client->request(
            'POST',
            $this->router->generate('experience-delete'),
            ['id' => [$id1 => 'on', $id2 => 'on']]
        );

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $this->em->clear();
        $this->assertNull($this->em->getRepository(Experience::class)->find($id1));
        $this->assertNull($this->em->getRepository(Experience::class)->find($id2));
    }

    public function testDeleteNonexistentExperience(): void
    {
        $this->client->request(
            'POST',
            $this->router->generate('experience-delete'),
            ['id' => [99999 => 'on']]
        );

        $this->assertResponseRedirects($this->router->generate('experience-list'));
    }

    public function testCreateExperienceWithoutLogin(): void
    {
        $this->client->restart();
        
        $this->client->request('GET', $this->router->generate('experience-create'));
        
        $this->assertResponseRedirects($this->router->generate('user-login'));
    }

    public function testUpdateExperienceWithoutLogin(): void
    {
        $this->client->restart();
    
        $this->client->request(
            'GET',
            $this->router->generate('experience-update', ['id' => 999])
        );
        
        $this->assertResponseRedirects($this->router->generate('user-login'));
    }

    public function testDeleteExperienceWithoutLogin(): void
    {
        $this->client->restart();
        $container = $this->getContainer();
        $router = $container->get('router');
        
        $this->client->request(
            'POST',
            $router->generate('experience-delete'),
            ['id' => [99999 => 'on']]
        );

        $this->assertResponseRedirects($router->generate('user-login'));
    }

    public function testExperienceFormFieldsExist(): void
    {
        $this->client->request('GET', $this->router->generate('experience-create'));

        $this->assertSelectorExists('input[name="experience[name]"]');
        $this->assertSelectorExists('textarea[name="experience[description]"]');
        $this->assertSelectorExists('input[name="experience[location]"]');
        $this->assertSelectorExists('input[name="experience[company]"]');
        $this->assertSelectorExists('input[name="experience[fromDate]"]');
    }

    public function testUpdateNonexistentExperience(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('experience-update', ['id' => 99999])
        );
        
        $this->assertTrue($this->client->getResponse()->getStatusCode() !== Response::HTTP_OK);
    }
}
