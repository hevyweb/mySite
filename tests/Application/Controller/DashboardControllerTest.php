<?php

namespace App\Tests\Application\Controller;

use Symfony\Component\HttpFoundation\Response;

class DashboardControllerTest extends AbstractApplicationTestCase
{
    public function testDashboardIndex(): void
    {
        $this->logInAdmin();
        $this->client->request('GET', $this->router->generate('admin-dashboard'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Admin panel');
    }

    public function testDashboardAccessDeniedForRegularUser(): void
    {
        $this->logInUser();
        $this->client->request('GET', $this->router->generate('admin-dashboard'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDashboardRedirectsForAnonymous(): void
    {
        $this->client->request('GET', $this->router->generate('admin-dashboard'));
        $this->assertResponseRedirects($this->router->generate('user-login'));
    }
}
