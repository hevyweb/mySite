<?php

namespace App\Tests\Application\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    public function testImageUploadAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        $this->client->request('POST', $this->router->generate('images-upload'));
        $this->assertResponseStatusCodeSame(403);
    }

    public function testImageUploadRedirectsForAnonymous(): void
    {
        $this->client->request('POST', $this->router->generate('images-upload'));
        $this->assertResponseRedirects($this->router->generate('user-login'));
    }
}
