<?php

namespace App\Tests\Application\Controller;

class TagControllerTest extends AbstractApplicationTestCase
{
    public function testTagSearch(): void
    {
        $this->logInAdmin();

        $this->client->request('GET', $this->router->generate('tag-list', ['name' => 'pl']));

        $this->assertEquals(
            json_encode([
                ['name' => 'Apple'],
                ['name' => 'Plum'],
            ]),
            $this->client->getResponse()->getContent()
        );
    }

    public function testTagAccessDenied(): void
    {
        $this->client->request('GET', $this->router->generate('tag-list', ['name' => 'pl']));
        $this->assertResponseRedirects($this->router->generate('user-login'));
    }
}
