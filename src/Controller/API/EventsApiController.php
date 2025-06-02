<?php

namespace App\Controller\API;

use App\DTO\EventDTO;
use App\Entity\Events;
use App\Form\EventType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/api/events')]
class EventsApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventsRepository $eventsRepository,
        private SerializerInterface $serializer
    ) {}

    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'api_events_index', methods: ['GET'])]
    public function index(PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $query = $this->eventsRepository->createQueryBuilder('e')->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $items = array_map(function ($event) {
            return (new EventDTO($event))->toArray();
        }, $pagination->getItems());

        return new JsonResponse([
            'items' => $items,
            'currentPage' => $pagination->getCurrentPageNumber(),
            'totalItems' => $pagination->getTotalItemCount(),
        ]);
    }

    #[Route('', name: 'api_events_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $model = new Events();
        $form = $this->createForm(EventType::class, $model);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($model);
            $this->entityManager->flush();

            $data = $this->serializer->serialize($model, 'json');
            return new JsonResponse(json_decode($data), JsonResponse::HTTP_CREATED);
        }

        return new JsonResponse([
            'errors' => (string) $form->getErrors(true, false)
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_events_show', methods: ['GET'])]
    public function show($id): JsonResponse
    {
        $model = $this->eventsRepository->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Event not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($model, 'json');
        return new JsonResponse(json_decode($data));
    }

    #[Route('/{id}', name: 'api_events_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        $model = $this->eventsRepository->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Event not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(EventType::class, $model);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $data = $this->serializer->serialize($model, 'json');
            return new JsonResponse(json_decode($data));
        }

        return new JsonResponse([
            'errors' => (string) $form->getErrors(true, false)
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'api_events_delete', methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        $model = $this->eventsRepository->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Event not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($model);
        $this->entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
