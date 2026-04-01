<?php

namespace App\Tests\Application\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageControllerTest extends AbstractApplicationTestCase
{
    public function testImageUpload(): void
    {
        $this->logInAdmin();
        
        $imagePath = __DIR__ . '/Resource/test.jpg';
        $uploadedFile = new UploadedFile(
            $imagePath,
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
        
        // Clean up uploaded file if possible, or assume tearDown handles it if tracked.
        // For simplicity in this test, we just verify the response.
    }

    public function testImageUploadAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        $this->client->request('POST', $this->router->generate('images-upload'));
        $this->assertResponseStatusCodeSame(403);
    }
}
