<?php

namespace App\Tests\Controller\API;

use App\Tests\Services\TestAuthService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class TasksApiControllerTest extends WebTestCase
{
    private function userLogin($container, $client)
    {
        $myService = $container->get(TestAuthService::class);
        $user = $myService->createUser();
        $client->loginUser($user);
    }


}
