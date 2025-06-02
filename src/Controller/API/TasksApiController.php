<?php

namespace App\Controller\API;

use App\DTO\TaskDTO;
use App\Entity\Events;
use App\Form\EventType;
use App\Repository\TasksModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/api/tasks', name: 'api_tasks_')]
class TasksApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TasksModelRepository   $repository,
        private SerializerInterface    $serializer
    )
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $query = $this->repository->createQueryBuilder('e')->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $items = array_map(function ($model) {
            return (new TaskDTO($model))->toArray();
        }, $pagination->getItems());

        return new JsonResponse([
            'items' => $items,
            'currentPage' => $pagination->getCurrentPageNumber(),
            'totalItems' => $pagination->getTotalItemCount(),
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $model = new Events();
        $form = $this->createForm(EventType::class, $model, [
            'csrf_protection' => false,
        ]);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($model);
            $this->entityManager->flush();

            $data = (new TaskDTO($model))->toArray();

            return new JsonResponse($data, JsonResponse::HTTP_CREATED);
        }

        return new JsonResponse([
            'errors' => (string)$form->getErrors(true, false)
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show($id): JsonResponse
    {
        $model = $this->repository->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = (new TaskDTO($model))->toArray();
        return new JsonResponse($data);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        $model = $this->repository->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(EventType::class, $model, [
            'csrf_protection' => false,
        ]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $data = (new TaskDTO($model))->toArray();
            return new JsonResponse($data);
        }

        return new JsonResponse([
            'errors' => (string)$form->getErrors(true, false)
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        $model = $this->repository->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($model);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Task deleted successfully'
        ]);
    }
}
