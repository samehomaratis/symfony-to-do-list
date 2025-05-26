<?php

namespace App\Tests\Controller;

use App\Controller\EventsController;
use App\Repository\EventsRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;


class EventsControllerTest extends WebTestCase
{
    private $entityManager;
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
}
