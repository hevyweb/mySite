<?php

namespace App\Tests\Application\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ImageControllerTest extends AbstractApplicationTestCase
{
    private string $imagePath;
    private string $sourceImagePath;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->imagePath = __DIR__ . '/Resource/test.jpg';
        $this->sourceImagePath = __DIR__ . '/Resource/test_test.jpg';
        
        if (file_exists($this->sourceImagePath)) {
            copy($this->sourceImagePath, $this->imagePath);
        }
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->imagePath)) {
            unlink($this->imagePath);
        }
    }

    public function testImageIndex(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('images-list'));
        $this->assertResponseRedirects($this->router->generate('home'));
    }

    public function testImageUpload(): void
    {
        $this->logInAdmin();
        
        $uploadedFile = new UploadedFile(
            $this->imagePath,
            'test.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request('POST', $this->router->generate('images-upload'), [], ['file' => $uploadedFile]);
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('location', $responseData);
        $this->assertStringContainsString('/blog/', $responseData['location']);
    }

    public function testImageUploadError(): void
    {
        $this->logInAdmin();
        
        // Sending a POST request without the 'file' parameter should trigger an Exception in the controller
        // because $request->files->get('file') will be null and the file manager will fail.
        $this->client->request('POST', $this->router->generate('images-upload'));
        
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testImageUploadAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        $this->client->request('POST', $this->router->generate('images-upload'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testImageUploadRedirectsForAnonymous(): void
    {
        $this->client->request('POST', $this->router->generate('images-upload'));
        $this->assertResponseRedirects($this->router->generate('user-login'));
    }
}
