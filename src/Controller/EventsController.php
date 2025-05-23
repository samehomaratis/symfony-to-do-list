<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EventsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager,
                                private EventsRepository       $eventsRepository)
    {

    }

    #[IsGranted('ROLE_USER')]
    #[Route('/events', name: 'web_events')]
    public function index(
        PaginatorInterface $paginator,
        Request            $request
    ): Response
    {
        $query = $this->eventsRepository->createQueryBuilder('t')->getQuery();

        $pagination = $paginator->paginate(
            $query, // Query to paginate
            $request->query->getInt('page', 1), // Current page number
            10 // Items per page
        );

        return $this->render('events/index.html.twig', [
            'events' => $pagination,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/events/create', name: 'app_events_new')]
    public function create(
        Request $request
    ): Response
    {
        $model = new Events();
        $form = $this->createForm(EventType::class, $model);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($model);
            $this->entityManager->flush();

            return $this->redirectToRoute('web_events');
        }

        return $this->render('events/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/events/edit/{id}', name: 'app_events_edit')]
    public function edit(
        Request $request,
                $id
    ): Response
    {
        $model = $this->eventsRepository->find($id);

        if (!$model) {
            throw $this->createNotFoundException('Event not found');
        }

        $form = $this->createForm(EventType::class, $model);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($model);
            $this->entityManager->flush();

            return $this->redirectToRoute('web_events');
        }

        return $this->render('events/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/events/delete/{id}', name: 'app_events_delete')]
    public function delete(
        Request $request,
                $id
    ): Response
    {
        $model = $this->eventsRepository->find($id);

        if (!$model) {
            throw $this->createNotFoundException('Event not found');
        }

        $this->entityManager->remove($model);
        $this->entityManager->flush();
        return $this->redirectToRoute('web_events');
    }

}
