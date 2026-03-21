<?php

namespace App\Tests\Application\Controller;

use App\Entity\Experience;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ExperienceControllerTest extends AbstractApplicationTestCase
{
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
            ['experience[location]' => str_repeat('a', 129)],
            'This value is too long. It should have 128 characters or less.',
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
        $this->assertSelectorExists('table');
        $this->assertSelectorTextContains('h1', 'Experiences');
    }

    public function testCreateExperienceFormDisplay(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('experience-create'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Add working place');
        $this->assertSelectorExists('form[name="experience"]');
    }

    public function testCreateExperienceSuccess(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('experience-create'));

        $data = [
            'experience[name]' => 'Senior Developer',
            'experience[description]' => 'Developed and maintained web applications using Symfony and React. Led a team of 3 developers.',
            'experience[location]' => 'San Francisco, USA',
            'experience[company]' => 'Tech Company Inc',
            'experience[fromDate]' => '01.01.2020',
            'experience[toDate]' => '31.12.2022',
        ];

        $this->client->submitForm('Create', $data);

        $this->assertResponseRedirects($this->router->generate('experience-list'));
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $experience = $em->getRepository(Experience::class)->findOneBy(['name' => 'Senior Developer']);
        $this->assertNotNull($experience);
        $this->assertEquals('Senior Developer', $experience->getName());
        $this->assertEquals('Tech Company Inc', $experience->getCompany());
    }

    public function testCreateExperienceWithoutImage(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('experience-create'));

        $data = [
            'experience[name]' => 'Junior Developer',
            'experience[description]' => 'Started career as junior developer, learning full-stack web development.',
            'experience[location]' => 'New York, USA',
            'experience[fromDate]' => '15.03.2019',
        ];

        $this->client->submitForm('Create', $data);

        $this->assertResponseRedirects($this->router->generate('experience-list'));
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $experience = $em->getRepository(Experience::class)->findOneBy(['name' => 'Junior Developer']);
        $this->assertNotNull($experience);
        $this->assertNull($experience->getImage());
    }

    /**
     * @param array<string, string|null> $invalidData
     *
     * @dataProvider invalidExperienceDataProvider
     */
    public function testCreateExperienceValidationError(array $invalidData, string $errorMessage): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('experience-create'));

        $validData = [
            'experience[name]' => 'Test Position',
            'experience[description]' => 'This is a valid test description for the position.',
            'experience[location]' => 'Test Location',
            'experience[fromDate]' => '01.01.2020',
        ];

        $data = array_merge($validData, $invalidData);

        $this->client->submitForm('Create', $data);
        $this->assertSelectorTextContains('.form_validation_error', $errorMessage);
    }

    public function testUpdateExperienceSuccess(): void
    {
        $this->logInAdmin();
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        
        $experience = new Experience();
        $experience->setName('Old Position');
        $experience->setDescription('Old description for the position.');
        $experience->setLocation('Old Location');
        $experience->setLocale('en');
        $experience->setFromDate(new \DateTime('2020-01-01'));
        $em->persist($experience);
        $em->flush();

        $this->client->request('GET', $this->router->generate('experience-update', ['id' => $experience->getId()]));

        $this->client->submitForm('Update', [
            'experience[name]' => 'Updated Position',
            'experience[description]' => 'Updated description with new responsibilities.',
            'experience[location]' => 'Updated Location',
            'experience[company]' => 'Updated Company',
            'experience[fromDate]' => '01.01.2021',
        ]);

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $em->refresh($experience);
        $this->assertEquals('Updated Position', $experience->getName());
        $this->assertEquals('Updated Company', $experience->getCompany());
    }

    /**
     * @param array<string, string|null> $invalidData
     *
     * @dataProvider invalidExperienceDataProvider
     */
    public function testUpdateExperienceValidationError(array $invalidData, string $errorMessage): void
    {
        $this->logInAdmin();
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        
        $experience = new Experience();
        $experience->setName('Valid Position');
        $experience->setDescription('Valid description for the position.');
        $experience->setLocation('Valid Location');
        $experience->setLocale('en');
        $experience->setFromDate(new \DateTime('2020-01-01'));
        $em->persist($experience);
        $em->flush();

        $this->client->request('GET', $this->router->generate('experience-update', ['id' => $experience->getId()]));

        $validData = [
            'experience[name]' => 'Updated Position',
            'experience[description]' => 'Updated valid description.',
            'experience[location]' => 'Updated Location',
            'experience[fromDate]' => '01.01.2021',
        ];

        $data = array_merge($validData, $invalidData);

        $this->client->submitForm('Update', $data);
        $this->assertSelectorTextContains('.form_validation_error', $errorMessage);
    }

    public function testTreeViewExperiencesByLocale(): void
    {
        $this->client->request('GET', $this->router->generate('experience-tree'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'My working experience');
    }

    public function testDeleteExperienceSuccess(): void
    {
        $this->logInAdmin();
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        
        $experience = new Experience();
        $experience->setName('Position To Delete');
        $experience->setDescription('This position will be deleted.');
        $experience->setLocation('Delete Location');
        $experience->setLocale('en');
        $experience->setFromDate(new \DateTime('2020-01-01'));
        $em->persist($experience);
        $em->flush();
        $experienceId = $experience->getId();

        $this->client->request(
            'GET',
            $this->router->generate('experience-delete'),
            ['id' => $experienceId]
        );

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $deletedExperience = $em->getRepository(Experience::class)->find($experienceId);
        $this->assertNull($deletedExperience);
    }

    public function testDeleteMultipleExperiencesSuccess(): void
    {
        $this->logInAdmin();
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        
        $experience1 = new Experience();
        $experience1->setName('Position 1');
        $experience1->setDescription('Description 1');
        $experience1->setLocation('Location 1');
        $experience1->setLocale('en');
        $experience1->setFromDate(new \DateTime('2020-01-01'));
        $em->persist($experience1);

        $experience2 = new Experience();
        $experience2->setName('Position 2');
        $experience2->setDescription('Description 2');
        $experience2->setLocation('Location 2');
        $experience2->setLocale('en');
        $experience2->setFromDate(new \DateTime('2021-01-01'));
        $em->persist($experience2);

        $em->flush();
        $id1 = $experience1->getId();
        $id2 = $experience2->getId();

        $this->client->request(
            'GET',
            $this->router->generate('experience-delete'),
            ['id' => [$id1, $id2]]
        );

        $this->assertResponseRedirects($this->router->generate('experience-list'));

        $this->assertNull($em->getRepository(Experience::class)->find($id1));
        $this->assertNull($em->getRepository(Experience::class)->find($id2));
    }

    public function testDeleteNonexistentExperience(): void
    {
        $this->logInAdmin();
        
        $this->client->request(
            'GET',
            $this->router->generate('experience-delete'),
            ['id' => 99999]
        );

        $this->assertResponseRedirects($this->router->generate('experience-list'));
    }

    public function testCreateExperienceWithoutLogin(): void
    {
        $this->client->request('GET', $this->router->generate('experience-create'));
        
        $this->assertTrue(
            $this->client->getResponse()->getStatusCode() === Response::HTTP_FOUND ||
            $this->client->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN
        );
    }

    public function testUpdateExperienceWithoutLogin(): void
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $experience = new Experience();
        $experience->setName('Test');
        $experience->setDescription('Test description.');
        $experience->setLocation('Test Location');
        $experience->setLocale('en');
        $experience->setFromDate(new \DateTime());
        $em->persist($experience);
        $em->flush();

        $this->client->request(
            'GET',
            $this->router->generate('experience-update', ['id' => $experience->getId()])
        );

        $this->assertTrue(
            $this->client->getResponse()->getStatusCode() === Response::HTTP_FOUND ||
            $this->client->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN
        );
    }

    public function testDeleteExperienceWithoutLogin(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('experience-delete'),
            ['id' => 1]
        );

        $this->assertTrue(
            $this->client->getResponse()->getStatusCode() === Response::HTTP_FOUND ||
            $this->client->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN
        );
    }

    public function testExperienceFormFieldsExist(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('experience-create'));

        $this->assertSelectorExists('input[name="experience[name]"]');
        $this->assertSelectorExists('textarea[name="experience[description]"]');
        $this->assertSelectorExists('input[name="experience[location]"]');
        $this->assertSelectorExists('input[name="experience[company]"]');
        $this->assertSelectorExists('input[name="experience[fromDate]"]');
    }

    public function testUpdateNonexistentExperience(): void
    {
        $this->logInAdmin();
        $this->client->request(
            'GET',
            $this->router->generate('experience-update', ['id' => 99999])
        );
        
        $this->assertTrue($this->client->getResponse()->getStatusCode() !== Response::HTTP_OK);
    }
}