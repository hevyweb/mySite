<?php

namespace App\Tests\Application\Controller;

use App\Entity\Condolence;
use App\Entity\Grave;
use App\Entity\PersonPhoto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class NecropolisControllerTest extends AbstractApplicationTestCase
{
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        parent::setUp();
        $this->em = $this->getContainer()->get(EntityManagerInterface::class);
    }

    public function testIndex(): void
    {
        $this->logInAdmin();

        $this->client->request('GET', '/administrator/necropolis/graves');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Graves');
    }

    public function testCreate(): void
    {
        $this->logInAdmin();

        $crawler = $this->client->request('GET', '/administrator/necropolis/grave/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Add grave details');

        $form = $crawler->selectButton('Create')->form();

        // Fill all possible fields for creation
        $form['grave[firstName]'] = 'JohnCreated';
        $form['grave[lastName]'] = 'DoeCreated';
        $form['grave[maidenName]'] = 'SmithCreated';
        $form['grave[gender]'] = '1'; // MALE
        $form['grave[birthDate]'] = '1950-05-10';
        $form['grave[deathDate]'] = '2020-01-15';
        $form['grave[birthPlace]'] = 'Birth City A';
        $form['grave[birthPlaceCoordinates]'] = '12.3456, 78.9012';
        $form['grave[deathPlace]'] = 'Death City B';
        $form['grave[deathPlaceCoordinates]'] = '23.4567, 89.0123';
        $form['grave[gravePlace]'] = 'Cemetery Sector 1';
        $form['grave[gravePlaceCoordinates]'] = '34.5678, 90.1234';
        $form['grave[epitaph]'] = 'Rest in Peace Test';
        $form['grave[biography]'] = 'This is a test biography description for creation.';
        $form['grave[isPublished]'] = '1';

        $dummyFile = __DIR__.'/Resource/test_test.jpg';
        $uploadedFile = new UploadedFile($dummyFile, 'test.jpg', 'image/jpeg', null, true);
        /* @phpstan-ignore offsetAssign.valueType */
        $form['grave[image]'] = $uploadedFile;

        $this->client->submit($form);
        $this->assertResponseRedirects();

        /** @var Grave|null $grave */
        $grave = $this->em->getRepository(Grave::class)->findOneBy(['firstName' => 'JohnCreated']);

        $this->assertNotNull($grave, 'Grave should exist in the database after creation.');
        $this->assertSame('DoeCreated', $grave->getLastName());
        $this->assertSame('SmithCreated', $grave->getMaidenName());
        $this->assertSame(1, $grave->getGender());
        $this->assertEquals('1950-05-10', $grave->getBirthDate()?->format('Y-m-d'));
        $this->assertEquals('2020-01-15', $grave->getDeathDate()->format('Y-m-d'));
        $this->assertSame('Birth City A', $grave->getBirthPlace());
        $this->assertSame('12.3456, 78.9012', $grave->getBirthPlaceCoordinates());
        $this->assertSame('Death City B', $grave->getDeathPlace());
        $this->assertSame('23.4567, 89.0123', $grave->getDeathPlaceCoordinates());
        $this->assertSame('Cemetery Sector 1', $grave->getGravePlace());
        $this->assertSame('34.5678, 90.1234', $grave->getGravePlaceCoordinates());
        $this->assertSame('Rest in Peace Test', $grave->getEpitaph());
        $this->assertSame('This is a test biography description for creation.', $grave->getBiography());
        $this->assertTrue($grave->isPublished());
    }

    public function testEdit(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $crawler = $this->client->request('GET', '/administrator/necropolis/grave/'.$grave->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Update grave details');

        $form = $crawler->selectButton('Update')->form();

        // Update all fields with new values
        $form['grave[firstName]'] = 'JonathanUpdated';
        $form['grave[lastName]'] = 'DoeUpdated';
        $form['grave[maidenName]'] = 'JohnsonUpdated';
        $form['grave[gender]'] = '2'; // FEMALE
        $form['grave[birthDate]'] = '1955-06-12';
        $form['grave[deathDate]'] = '2022-03-20';
        $form['grave[birthPlace]'] = 'New Birth City';
        $form['grave[birthPlaceCoordinates]'] = '11.1111, 22.2222';
        $form['grave[deathPlace]'] = 'New Death City';
        $form['grave[deathPlaceCoordinates]'] = '33.3333, 44.4444';
        $form['grave[gravePlace]'] = 'New Cemetery Sector';
        $form['grave[gravePlaceCoordinates]'] = '55.5555, 66.6666';
        $form['grave[epitaph]'] = 'Updated Epitaph Text';
        $form['grave[biography]'] = 'Updated biography description content.';
        /* @phpstan-ignore method.nonObject */
        $form['grave[isPublished]']->untick();
        $dummyFile = __DIR__.'/Resource/test_test.jpg';
        $uploadedFile = new UploadedFile($dummyFile, 'test.jpg', 'image/jpeg', null, true);
        /* @phpstan-ignore offsetAssign.valueType */
        $form['grave[image]'] = $uploadedFile;

        $this->client->submit($form);
        $this->assertResponseRedirects();

        // refresh the entity from the database
        $grave = $this->em->getRepository(Grave::class)->find($grave->getId());

        $this->assertNotNull($grave);
        $this->assertSame('JonathanUpdated', $grave->getFirstName());
        $this->assertSame('DoeUpdated', $grave->getLastName());
        $this->assertSame('JohnsonUpdated', $grave->getMaidenName());
        $this->assertSame(2, $grave->getGender());
        $this->assertEquals('1955-06-12', $grave->getBirthDate()?->format('Y-m-d'));
        $this->assertEquals('2022-03-20', $grave->getDeathDate()->format('Y-m-d'));
        $this->assertSame('New Birth City', $grave->getBirthPlace());
        $this->assertSame('11.1111, 22.2222', $grave->getBirthPlaceCoordinates());
        $this->assertSame('New Death City', $grave->getDeathPlace());
        $this->assertSame('33.3333, 44.4444', $grave->getDeathPlaceCoordinates());
        $this->assertSame('New Cemetery Sector', $grave->getGravePlace());
        $this->assertSame('55.5555, 66.6666', $grave->getGravePlaceCoordinates());
        $this->assertSame('Updated Epitaph Text', $grave->getEpitaph());
        $this->assertSame('Updated biography description content.', $grave->getBiography());
        $this->assertFalse($grave->isPublished());
    }

    public function testDelete(): void
    {
        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $this->logInAdmin();

        $this->client->request('GET', '/administrator/necropolis/grave/'.$grave->getId().'/delete');

        $this->assertResponseRedirects($this->router->generate('necropolis-list'));
        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $this->assertNull($grave);
    }

    public function testGrave(): void
    {
        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $this->logInAdmin();

        $this->client->request('GET', '/grave/'.$grave->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Michael Joseph Jackson');
    }

    public function testGraveNotFound(): void
    {
        $this->logInAdmin();

        $this->client->request('GET', '/grave/019ed78f-3271-7ca3-bc20-20215562c574');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGraveNotPublished(): void
    {
        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $grave->setIsPublished(false);
        $this->em->flush();

        $this->client->request('GET', '/grave/'.$grave->getId());
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPhotosPage(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/photos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Image'); // Matches translated title in controller
    }

    public function testPhotosCreate(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $crawler = $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/photos');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save Photo')->form(); // Adjust button text if different in your GravePhotoType form

        $dummyFile = __DIR__.'/Resource/test_test.jpg';
        $uploadedFile = new UploadedFile($dummyFile, 'gallery_test.jpg', 'image/jpeg', null, true);

        /* @phpstan-ignore offsetAssign.valueType */
        $form['grave_photo[filename]'] = $uploadedFile;
        $form['grave_photo[description]'] = 'Test gallery photo description';
        $form['grave_photo[shootingDate]'] = '2023-11-23';

        $this->client->submit($form);
        $this->assertResponseRedirects('/administrator/grave/'.$grave->getId().'/photos');

        $this->em->clear();
        $grave = $this->em->getRepository(Grave::class)->find($grave->getId());

        $this->assertCount(2, $grave->getPersonPhotos());
        $photo = $grave->getPersonPhotos()->filter(function ($photo) {
            return 'Test gallery photo description' === $photo->getDescription()
                && '23.11.2023' === $photo->getShootingDate()?->format('d.m.Y');
        });
        $this->assertNotFalse($photo->first(), 'Photo should be found in the database.');
    }

    public function testPhotosList(): void
    {
        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $this->client->request('GET', '/grave/'.$grave->getId().'/photos');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
    }

    public function testPhotosEdit(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $photo = $grave->getPersonPhotos()->first();
        $this->assertNotFalse($photo, 'Grave must have a photo to test editing.');

        $this->client->request('POST', '/administrator/grave/'.$grave->getId().'/photos/'.$photo->getId().'/edit', [
            'description' => 'Updated gallery photo description',
            'shootingDate' => '2024-01-10',
        ]);

        $this->assertResponseRedirects();

        // Verify updates in database
        $this->em->clear();
        /** @var PersonPhoto $updatedPhoto */
        $updatedPhoto = $this->em->getRepository(PersonPhoto::class)->find($photo->getId());

        $this->assertSame('Updated gallery photo description', $updatedPhoto->getDescription());
        $this->assertEquals('2024-01-10', $updatedPhoto->getShootingDate()?->format('Y-m-d'));
    }

    public function testPhotosDelete(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $photo = $grave->getPersonPhotos()->first();
        $this->assertNotFalse($photo, 'Grave must have a photo to test deletion.');
        $photoId = $photo->getId();

        $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/photos/'.$photoId.'/delete');

        $this->assertResponseRedirects();

        // Verify the photo is removed from database
        $this->em->clear();
        $deletedPhoto = $this->em->getRepository(PersonPhoto::class)->find($photoId);
        $this->assertNull($deletedPhoto);
    }

    public function testPhotosDeleteGraveNotFound(): void
    {
        $this->logInAdmin();

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->client->request('GET', '/administrator/grave/'.$nonExistentId.'/photos/999/delete');

        $this->assertResponseRedirects('/administrator/necropolis/graves');

        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-danger, .flash-error, div:contains("Grave not found")');
    }

    public function testPhotosDeletePhotoNotFound(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);

        $this->assertNotNull($grave, 'Grave should exist for this test.');

        $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/photos/999999/delete');

        $this->assertResponseRedirects('/administrator/grave/'.$grave->getId().'/photos');

        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-danger, .flash-error, div:contains("Grave photo not found")');
    }

    public function testPhotosEditGraveNotFound(): void
    {
        $this->logInAdmin();

        $nonExistentGraveId = '00000000-0000-0000-0000-000000000000';

        $this->client->request('GET', '/administrator/grave/'.$nonExistentGraveId.'/photos/999/edit', [
            'description' => 'Updated description',
        ]);

        $this->assertResponseRedirects('/administrator/necropolis/graves');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger, .flash-error, div:contains("Grave not found")');
    }

    public function testPhotosEditPhotoNotFound(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $this->assertNotNull($grave);

        $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/photos/999999/edit', [
            'description' => 'Updated description',
        ]);

        $this->assertResponseRedirects('/administrator/grave/'.$grave->getId().'/photos');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger, .flash-error, div:contains("Grave photo not found")');
    }

    public function testPhotosEditPhotoDoesNotBelongToGrave(): void
    {
        $this->logInAdmin();

        $graves = $this->em->getRepository(Grave::class)->findAll();
        $this->assertGreaterThanOrEqual(2, count($graves));

        $graveA = $graves[0];
        $graveB = $graves[1];

        $photoB = $graveB->getPersonPhotos()->first();

        if (!$photoB) {
            $photoB = new PersonPhoto();
            $photoB->setFilename('dummy_test.jpg');
            $photoB->setDescription('Belongs to Grave B');
            $photoB->setGrave($graveB);
            $this->em->persist($photoB);
            $this->em->flush();
        }

        $this->client->request('GET', '/administrator/grave/'.$graveA->getId().'/photos/'.$photoB->getId().'/edit', [
            'description' => 'Trying to hijack photo edit',
        ]);

        $this->assertResponseRedirects('/administrator/grave/'.$graveA->getId().'/photos');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger, .flash-error, div:contains("Grave photo does not belong to this grave")');
    }

    public function testCondolencesIndex(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $this->assertNotNull($grave);

        $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/condolences');
        $this->assertResponseIsSuccessful();
    }

    public function testCondolencesNewSuccess(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $this->assertNotNull($grave);

        $crawler = $this->client->request('GET', '/grave/'.$grave->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Надіслати')->form(); // Adjust button text if different in your form

        $form['condolence[name]'] = 'Jane Smith';
        $form['condolence[description]'] = 'Your music will live on forever.';

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);

        $condolence = $this->em->getRepository(Condolence::class)->findOneBy([
            'name' => 'Jane Smith',
        ]);
        $this->assertNotNull($condolence);
        $this->assertEquals('Your music will live on forever.', $condolence->getDescription());
    }

    public function testCondolencesNewValidationError(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $this->assertNotNull($grave);

        $crawler = $this->client->request('GET', '/grave/'.$grave->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Надіслати')->form();

        $form['condolence[name]'] = '';
        $form['condolence[description]'] = '';

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testCondolencesToggleStatus(): void
    {
        $this->logInAdmin();

        $condolence = $this->em->getRepository(Condolence::class)->findOneBy([
            'name' => 'John Doe',
        ]);
        $this->assertNotNull($condolence);

        $initialStatus = $condolence->isActive();

        $this->client->request('GET', '/administrator/grave/'.$condolence->getGrave()->getId().'/condolences/'.$condolence->getId().'/toggle-status');
        $this->assertResponseRedirects('/administrator/grave/'.$condolence->getGrave()->getId().'/condolences');

        $this->em->clear();
        $updatedCondolence = $this->em->getRepository(Condolence::class)->find($condolence->getId());
        $this->assertEquals(!$initialStatus, $updatedCondolence->isActive());
    }

    public function testCondolencesDelete(): void
    {
        $this->logInAdmin();

        $condolence = $this->em->getRepository(Condolence::class)->findOneBy([
            'name' => 'John Doe',
        ]);
        $this->assertNotNull($condolence);

        $graveId = $condolence->getGrave()->getId();
        $condolenceId = $condolence->getId();

        $this->client->request('GET', '/administrator/grave/'.$graveId.'/condolences/'.$condolenceId.'/delete');
        $this->assertResponseRedirects('/administrator/grave/'.$graveId.'/condolences');

        $this->em->clear();
        $deletedCondolence = $this->em->getRepository(Condolence::class)->find($condolenceId);
        $this->assertNull($deletedCondolence);
    }

    public function testCondolencesDeleteNotFound(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $this->assertNotNull($grave);

        $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/condolences/999999/delete');
        $this->assertResponseRedirects('/administrator/necropolis/graves');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger, .flash-error, div:contains("Grave condolence not found")');
    }

    public function testCondolencesToggleStatusNotFound(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $this->assertNotNull($grave);

        $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/condolences/999999/toggle-status');
        $this->assertResponseRedirects('/administrator/necropolis/graves');

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger, .flash-error, div:contains("Grave condolence not found")');
    }

    public function testQrcodeSuccess(): void
    {
        $this->logInAdmin();

        $grave = $this->em->getRepository(Grave::class)->findOneBy([
            'firstName' => 'Michael',
            'lastName' => 'Jackson',
        ]);
        $this->assertNotNull($grave);

        $this->client->request('GET', '/administrator/grave/'.$grave->getId().'/qrcode');

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('qrcode', $data);
        $this->assertNotEmpty($data['qrcode']);
    }

    public function testQrcodeGraveNotFound(): void
    {
        $this->logInAdmin();

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->client->request('GET', '/administrator/grave/'.$nonExistentId.'/qrcode');

        $this->assertResponseStatusCodeSame(404);
    }
}
