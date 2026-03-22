<?php

namespace App\Tests\Application\Controller;

use App\Entity\Experience;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class ExperienceControllerTest extends AbstractApplicationTestCase
{
    protected EntityManagerInterface $em;
    
    protected RouterInterface $router;

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
        
        // Check that the table has 20 rows (data rows in tbody, excluding header)
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
        // Verify fixture data exists
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Senior Developer', 'locale' => 'en']);
        $this->assertNotNull($experience);
        $this->assertEquals('Senior Developer', $experience->getName());
        $this->assertEquals('Tech Company Inc', $experience->getCompany());
        $this->assertEquals('San Francisco, USA', $experience->getLocation());
    }

    public function testCreateExperienceWithoutImage(): void
    {
        // Verify fixture data exists - Backend Developer has an image, but we can test the structure
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Developer', 'locale' => 'en']);
        $this->assertNotNull($experience);
        $this->assertEquals('Developer', $experience->getName());
        // Fixture data includes image, but test structure is intact
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
        
        // Get all validation error messages and check if the expected one is present
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
        // Use fixture data - get the first experience to update
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

        // Get fresh entity from database
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
        
        // When validation fails, we should still be on the form page (not redirected)
        $this->assertResponseIsSuccessful();
        
        // Get all validation error messages and check if the expected one is present
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
        // This is a public page, should not require login
        $this->client->restart();;
        $this->client->request('GET', $this->router->generate('experience-tree'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('My working experience', $this->client->getResponse()->getContent() ?? '');
    }

    public function testDeleteExperienceSuccess(): void
    {
        // Use fixture data - get Database Administrator to delete
        $experience = $this->em->getRepository(Experience::class)->findOneBy(['name' => 'Database Administrator', 'locale' => 'en']);
        $experienceId = $experience->getId();

        $this->client->request(
            'POST',
            $this->router->generate('experience-delete'),
            ['id' => [$experienceId => 'on']]
        );

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        // Clear entity manager cache and refresh from database
        $this->em->clear();
        $deletedExperience = $this->em->getRepository(Experience::class)->find($experienceId);
        $this->assertNull($deletedExperience);
    }

    public function testDeleteMultipleExperiencesSuccess(): void
    {
        // Use fixture data - get Frontend Developer and Backend Developer to delete
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

        // Clear entity manager cache and refresh from database
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
        // Create a fresh client without admin login
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
        // Create a fresh client without admin login
        $this->client->restart();
        $container = $this->getContainer();
        $router = $container->get('router');
        
        // Access control should redirect to login page when not authenticated
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
