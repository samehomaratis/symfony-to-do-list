<?php

namespace App\Tests\Controller\API;

use App\Entity\UserModal;
use App\Repository\UserModalRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;

class AuthControllerTest extends WebTestCase
{

    public function testLoginInvalidCredentials(): void
    {
        $client = static::createClient();

        $mockRepo = $this->createMock(UserModalRepository::class);
        $mockRepo->method('findOneBy')->willReturn(null); // Simulate user not found

        $client->getContainer()->set(UserModalRepository::class, $mockRepo);

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'fake@example.com',
            'password' => 'wrongpass'
        ]));

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
            'Invalid credentials',
            $client->getResponse()->getContent()
        );
    }

    public function testLoginSuccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $repo = $container->get(UserModalRepository::class);

        $model = new UserModal();
        $model->setPassword('correct-password');

        // Simulate a logged-in user with ROLE_USER if needed
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $hashedPassword = $passwordHasher->hashPassword($model, $model->getPassword());

        $user = $repo->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => $hashedPassword
        ]);


        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'password' => 'correct-password'
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testRegisterMissingFields(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
            json_encode(['error' => 'Name, email and password are required']),
            $client->getResponse()->getContent()
        );
    }

    public function testRegisterInvalidForm(): void
    {
        $client = static::createClient();

        $data = [
            'email' => 'bad-email', // Assume this is invalid
            'name' => '',
            'password' => ''
        ];

        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('errors', $client->getResponse()->getContent());
    }

    public function testRegisterSuccess(): void
    {
        $client = static::createClient();

        $mockHasher = $this->createMock(UserPasswordHasherInterface::class);
        $mockHasher->method('hashPassword')->willReturn('hashed-password');

        $mockJwt = $this->createMock(JWTTokenManagerInterface::class);
        $mockJwt->method('create')->willReturn('mocked-jwt-token');

        $client->getContainer()->set(UserPasswordHasherInterface::class, $mockHasher);
        $client->getContainer()->set(JWTTokenManagerInterface::class, $mockJwt);

        $email = ByteString::fromRandom(16)->toString();

        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email . '@example.com',
            'name' => 'Valid User',
            'password' => 'secret123ggbdgds'
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
            json_encode(['token' => 'mocked-jwt-token']),
            $client->getResponse()->getContent()
        );
    }


}