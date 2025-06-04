<?php

namespace App\Tests\Controller\API;

use App\Repository\EventsRepository;
use App\Repository\TasksModelRepository;
use App\Tests\Services\TestAuthService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TasksApiControllerTest extends WebTestCase
{
    private function userLogin($container, $client)
    {
        $myService = $container->get(TestAuthService::class);
        $user = $myService->createUser();
        $client->loginUser($user);
        return $user;
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

    private function generateTask($user, $event)
    {
        $container = static::getContainer();

        $repo = $container->get(TasksModelRepository::class);
        $criteria = ['title' => 'Test Task Title', 'user_id' => $user->getId()];
        $due_date = new \DateTime('2025-06-01');

        $data = [
            'description' => 'Task description',
            'due_date' => $due_date,
            'status' => '0',
            'priority' => '0',
            'event_id' => $event->getId(),
        ];
        return $repo->updateOrCreate($criteria, $data);
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $this->userLogin($container, $client);

        $client->request('GET', '/api/tasks');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('items', $data);
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $user = $this->userLogin($container, $client);
        $event = $this->generateEvent();

        $client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Sample Task',
            'description' => 'This is a test task',
            'event_id' => $event->getId(), // replace with a real event ID or mock it
            'user_id' => $user->getId(), // ensure the user ID is set
            'due_date' => '2025-06-01 10:00:00',
            'status' => 0, // adjust as needed
            'priority' => 0, // adjust as needed
        ]));


        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $data);
    }

    public function testShow(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $user = $this->userLogin($container, $client);

        $event = $this->generateEvent();
        $task = $this->generateTask($user, $event);

        $client->request('GET', '/api/tasks/' . $task->getId());

        $response = $client->getResponse();
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('id', $data);
        } else {
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $user = $this->userLogin($container, $client);

        $event = $this->generateEvent();
        $task = $this->generateTask($user, $event);

        $client->request('PUT', '/api/tasks/' . $task->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Updated Task Title',
            'description' => 'This is a test task',
            'event_id' => $event->getId(), // replace with a real event ID or mock it
            'user_id' => $user->getId(), // ensure the user ID is set
            'due_date' => '2025-06-01 10:00:00',
            'status' => 0, // adjust as needed
            'priority' => 0, // adjust as needed
        ]));

        $response = $client->getResponse();
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertEquals('Updated Task Title', $data['title']);
        } elseif ($response->getStatusCode() === Response::HTTP_UNPROCESSABLE_ENTITY) {
            $this->assertStringContainsString('Event not found', $response->getContent());
        } else {
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $user = $this->userLogin($container, $client);

        $event = $this->generateEvent();
        $task = $this->generateTask($user, $event);

        $client->request('DELETE', '/api/tasks/' . $task->getId());

        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();

        if ($statusCode === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertEquals('Task deleted successfully', $data['message']);
        } else {
            $this->assertEquals(Response::HTTP_NOT_FOUND, $statusCode);
        }
    }
}
