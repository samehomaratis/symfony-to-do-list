<?php

namespace App\Tests\Controller;

use App\Controller\ToDoListController;
use App\Repository\EventsRepository;
use App\Repository\TasksModelRepository;
use App\Tests\Services\TestAuthService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ToDoListControllerTest extends WebTestCase
{
    private $taskRepository;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TasksModelRepository::class);
    }

    public function testIndex(): void
    {
        $container = static::getContainer();
        $controller = $container->get(ToDoListController::class);
        $paginator = $container->get(PaginatorInterface::class);

        // Set up the QueryBuilder
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuery'])
            ->getMock();

        // Use a real Query object
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder->method('getQuery')->willReturn($query);
        $this->taskRepository->method('createQueryBuilder')->willReturn($queryBuilder);

        $request = new Request();
        $request->query->set('page', 1);

        $response = $controller->index($paginator, $request);

        $this->assertInstanceOf(Response::class, $response);
    }

    private function generateEvent()
    {
        $container = static::getContainer();

        $eventsRepository = $container->get(EventsRepository::class);
        $criteria = ['name' => 'Test Event'];
        $data = ['event_date' => '2025-07-01', 'event_time' => '09:10'];
        return $eventsRepository->updateOrCreate($criteria, $data);
    }

    private function generateForm($form, $user, $event)
    {
        $form['task[user_id]'] = $user->getId();
        $form['task[title]'] = 'Task Title';
        $form['task[description]'] = 'Task description';
        $form['task[event_date]'] = '2025-06-01'; // adjust as needed
        $form['task[status]'] = '0'; // adjust as needed
        $form['task[priority]'] = '0'; // adjust as needed
        $form['task[event_id ]'] = $event->getId();

        return $form;
    }

    private function generateTask($user, $event)
    {
        $container = static::getContainer();

        $repo = $container->get(TasksModelRepository::class);
        $criteria = ['title' => 'Test Task Title', 'user_id' => $user->getId()];
        $data = [
            'description' => 'Task description',
            'event_date' => '2025-06-01',
            'status' => '0',
            'priority' => '0',
            'event_id' => $event->getId(),
        ];
        return $repo->updateOrCreate($criteria, $data);
    }

    public function testCreateFormDisplaysAndSubmitsSuccessfully(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        // Simulate a logged-in user with ROLE_USER if needed
        $myService = $container->get(TestAuthService::class);

        $user = $myService->createUser();

        // Log in the user
        $client->loginUser($user);

        // GET request to display form
        $crawler = $client->request('GET', '/to-do-list/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $event = $this->generateEvent();

        // Fill and submit the form
        $form = $crawler->filter('#task_submit')->form();

        $form = $this->generateForm($form, $user, $event);

        $client->submit($form);

        $this->assertResponseRedirects('/to-do-list');
        $client->followRedirect();

        $this->assertSelectorTextContains('body', 'Test Task');
    }

    public function testEditValidFormSubmission(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        // Simulate a logged-in user with ROLE_USER if needed
        $myService = $container->get(TestAuthService::class);

        $user = $myService->createUser();

        // Log in the user
        $client->loginUser($user);

        // Simulate a logged-in user with ROLE_USER if needed
        $myService = $container->get(TestAuthService::class);

        $user = $myService->createUser();

        $event = $this->generateEvent();

        $task = $this->generateTask($user, $event);

        // GET request to display form
        $crawler = $client->request('GET', '/to-do-list/edit/' . $task->getId());

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('form');

        $form = $crawler->filter('#task_submit')->form();

        $form = $this->generateForm($form, $user, $task);

        $client->submit($form);

        $this->assertResponseRedirects('/to-do-list');
        $client->followRedirect();

        $this->assertSelectorTextContains('body', 'Test Task');
    }

    public function testDeleteSubmission(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        // Simulate a logged-in user with ROLE_USER if needed
        $myService = $container->get(TestAuthService::class);

        $user = $myService->createUser();

        // Log in the user
        $client->loginUser($user);

        // Simulate a logged-in user with ROLE_USER if needed
        $myService = $container->get(TestAuthService::class);

        $user = $myService->createUser();

        $event = $this->generateEvent();

        $task = $this->generateTask($user, $event);

        // GET request to display form
        $crawler = $client->request('GET', '/to-do-list/delete/' . $task->getId());

        $this->assertResponseRedirects('/to-do-list');
        $client->followRedirect();

        $this->assertSelectorTextContains('body', 'Test Task');
    }
}
