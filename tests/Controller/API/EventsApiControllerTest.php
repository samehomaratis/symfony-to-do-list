<?php

namespace App\Tests\Controller\API;

use App\Repository\EventsRepository;
use App\Tests\Services\TestAuthService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Events;

class EventsApiControllerTest extends WebTestCase
{
    private function userLogin($container, $client)
    {
        $myService = $container->get(TestAuthService::class);
        $user = $myService->createUser();
        $client->loginUser($user);
    }

    public function testIndex()
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->userLogin($container, $client);

        $client->request('GET', '/api/events');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('items', $data);
    }

    private function generateEvent()
    {
        $container = static::getContainer();

        $event_date = new \DateTime('2025-06-01');
        $event_time = new \DateTime('09:10');

        $eventsRepository = $container->get(EventsRepository::class);
        $criteria = ['name' => 'Test Event'];
        $data = ['event_date' => $event_date, 'event_time' => $event_time];
        return $eventsRepository->updateOrCreate($criteria, $data);
    }

    public function testCreate()
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->userLogin($container, $client);

        $client->request('POST', '/api/events', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Test Event',
            'event_date' => '2025-06-04',
            'event_time' => '10:00',
        ]));

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('Test Event', $data['name']);
    }

    public function testShow()
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->userLogin($container, $client);

        $event = $this->generateEvent();

        // Assuming an event with ID 1 exists
        $client->request('GET', '/api/events/' . $event->getId());
        $response = $client->getResponse();

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('id', $data);
        } else {
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }

    public function testEdit()
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->userLogin($container, $client);

        $event = $this->generateEvent();

        // Assuming event with ID 1 exists
        $client->request('PUT', '/api/events/' . $event->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Updated Event',
            'event_date' => '2025-06-04',
            'event_time' => '10:00',
        ]));

        $response = $client->getResponse();
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertEquals('Updated Event', $data['name']);
        } else {
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }

    public function testDelete()
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->userLogin($container, $client);

        $event = $this->generateEvent();

        // Assuming event with ID 1 exists
        $client->request('DELETE', '/api/events/' . $event->getId());

        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();

        if ($statusCode === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertEquals('Event deleted successfully', $data['message']);
        } else {
            $this->assertEquals(Response::HTTP_NOT_FOUND, $statusCode);
        }
    }
}
