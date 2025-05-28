<?php

namespace App\Tests\Controller;

use App\Controller\ToDoListController;
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

        // Fill and submit the form
        $form = $crawler->filter('#event_submit')->form();

        $form['event[name]'] = 'Test Event'; // adjust field names to your form
        $form['event[event_date]'] = '2025-06-01'; // adjust as needed
        $form['event[event_time]'] = '09:10'; // adjust as needed

        $client->submit($form);

        $this->assertResponseRedirects('/events');
        $client->followRedirect();

        $this->assertSelectorTextContains('body', 'Test Event');
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
        $taskRepository = $container->get(TasksModelRepository::class);

        $criteria = ['name' => 'Test Event'];
        $data = ['event_date' => '2025-07-01', 'event_time' => '09:10'];
        $event = $taskRepository->updateOrCreate($criteria, $data);

        // GET request to display form
        $crawler = $client->request('GET', '/to-do-list/edit/' . $event->getId());

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('form');

        $form = $crawler->filter('#event_submit')->form();

        $form['event[name]'] = 'Test Event'; // adjust field names to your form
        $form['event[event_date]'] = '2025-07-01'; // adjust as needed
        $form['event[event_time]'] = '09:35'; // adjust as needed

        $client->submit($form);

        $this->assertResponseRedirects('/events');
        $client->followRedirect();

        $this->assertSelectorTextContains('body', 'Test Event');
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
        $taskRepository = $container->get(TasksModelRepository::class);

        $criteria = ['name' => 'Test Event'];
        $data = ['event_date' => '2025-07-01', 'event_time' => '09:10'];
        $event = $taskRepository->updateOrCreate($criteria, $data);

        // GET request to display form
        $crawler = $client->request('GET', '/to-do-list/delete/' . $event->getId());

        $this->assertResponseRedirects('/events');
        $client->followRedirect();

        $this->assertSelectorTextContains('body', 'Test Event');
    }
}
