<?php

namespace App\Tests\Application\Controller;

use Symfony\Component\HttpFoundation\Response;

class RoleControllerTest extends AbstractApplicationTestCase
{
    public function testRoleListIndex(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('role-list'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Roles');
        $this->assertSelectorExists('table');
    }

    public function testRoleListAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('role-list'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRoleListRedirectsForAnonymous(): void
    {
        $this->client->request('GET', $this->router->generate('role-list'));
        $this->assertResponseRedirects($this->router->generate('user-login'));
    }
}
