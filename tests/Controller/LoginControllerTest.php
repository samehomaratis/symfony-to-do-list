<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        // Make the request
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
    }
}
