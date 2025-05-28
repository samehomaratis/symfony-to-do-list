<?php

namespace App\Tests\Controller;

use App\Controller\EventsController;
use App\Repository\EventsRepository;
use App\Tests\Services\TestAuthService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;


class EventsControllerTest extends WebTestCase
{
    private $eventsRepository;

    protected function setUp(): void
    {
        $this->eventsRepository = $this->createMock(EventsRepository::class);
    }

    public function testIndex(): void
    {
        $container = static::getContainer();
        $controller = $container->get(EventsController::class);
        $paginator = $container->get(PaginatorInterface::class);

        // Setup the QueryBuilder
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuery'])
            ->getMock();

        // Use a real Query object
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder->method('getQuery')->willReturn($query);
        $this->eventsRepository->method('createQueryBuilder')->willReturn($queryBuilder);

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
        $crawler = $client->request('GET', '/events/create');

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

        // GET request to display form
        $crawler = $client->request('GET', '/events/edit/' . $event->getId());

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('form');

        $form = $crawler->filter('#event_submit')->form();

        $form['event[name]'] = 'Test Event'; // adjust field names to your form
        $form['event[event_date]'] = '2025-07-01'; // adjust as needed
        $form['event[event_time]'] = '09:10'; // adjust as needed

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

        $event = $this->generateEvent();

        // GET request to display form
        $crawler = $client->request('GET', '/events/delete/' . $event->getId());

        $this->assertResponseRedirects('/events');
        $client->followRedirect();

        $this->assertSelectorTextContains('body', 'Test Event');
    }

}
