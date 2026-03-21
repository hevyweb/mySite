<?php

use PHPUnit\Framework\TestCase;

class ExperienceControllerTest extends TestCase
{
    public function testIndex()
    {
        // Simulate a GET request to the index method
        $response = $this->get('/experience');

        // Assert the response is successful
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShow()
    {
        // Simulate a GET request for a specific experience
        $response = $this->get('/experience/1');

        // Assert the response is successful
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getBody());
    }

    public function testCreate()
    {
        // Simulate a POST request to create a new experience
        $response = $this->post('/experience', [
            'title' => 'New Experience',
            'description' => 'Experience description',
        ]);

        // Assert the response redirects to the index
        $this->assertRedirects('/experience');
    }

    public function testUpdate()
    {
        // Simulate a PUT request to update an experience
        $response = $this->put('/experience/1', [
            'title' => 'Updated Experience'
        ]);

        // Assert the response is successful
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDelete()
    {
        // Simulate a DELETE request to remove an experience
        $response = $this->delete('/experience/1');

        // Assert the response redirects to the index
        $this->assertRedirects('/experience');
    }
}