<?php

namespace App\Tests\Controller;

use App\Tests\Services\TestAuthService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{


    public function testHomeRequiresAuthentication()
    {
        $client = static::createClient();
        $client->request('GET', '/home');

        // Should redirect to login
        $this->assertResponseRedirects('/login'); // adjust this if your login path is different
    }

    public function testAuthenticatedUserCanAccessHome()
    {
        $client = static::createClient();

        $container = static::getContainer();

        $myService = $container->get(TestAuthService::class);

        $user = $myService->createUser();

        // Log in the user
        $client->loginUser($user);

        // Make the request
        $crawler = $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
    }
}

