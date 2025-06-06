<?php

namespace App\Controller;

use App\Entity\TasksModel;
use App\Form\TaskType;
use App\Repository\TasksModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ToDoListController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager,
                                private TasksModelRepository   $tasksRepository)
    {

    }

    #[IsGranted('ROLE_USER')]
    #[Route('/to-do-list', name: 'web_tasks')]
    public function index(
        PaginatorInterface $paginator,
        Request            $request
    ): Response
    {
        $query = $this->tasksRepository->createQueryBuilder('t')->getQuery();

        $pagination = $paginator->paginate(
            $query, // Query to paginate
            $request->query->getInt('page', 1), // Current page number
            10 // Items per page
        );

        return $this->render('to_do_list/index.html.twig', [
            'tasks' => $pagination,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/to-do-list/create', name: 'app_tasks_new')]
    public function create(
        Request $request
    ): Response
    {
        $task = new TasksModel();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event_id = $request->request->all()['task']['event'] ?? null;
            $task->setEventId($event_id);;
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return $this->redirectToRoute('web_tasks');
        }

        return $this->render('to_do_list/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/to-do-list/edit/{id}', name: 'app_tasks_edit')]
    public function edit(
        Request $request,
                $id
    ): Response
    {
        $task = $this->tasksRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return $this->redirectToRoute('web_tasks');
        }

        return $this->render('to_do_list/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/to-do-list/toggle/{id}', name: 'app_tasks_toggle')]
    public function markAsComplete(
        Request $request,
                $id
    ): Response
    {
        $task = $this->tasksRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        if ($task->getStatus() == 0 || $task->getStatus() == 1) {
            $task->setStatus(2);
        } else {
            $task->setStatus(1);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->redirectToRoute('web_tasks');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/to-do-list/delete/{id}', name: 'app_tasks_delete')]
    public function delete(
        Request $request,
                $id
    ): Response
    {
        $task = $this->tasksRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();
        return $this->redirectToRoute('web_tasks');
    }

}
