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
    #[IsGranted('ROLE_USER')]
    #[Route('/to-do-list', name: 'web_tasks')]
    public function index(
        TasksModelRepository $tasksRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $query = $tasksRepository->createQueryBuilder('t')->getQuery();

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
        Request $request,
        EntityManagerInterface $entityManager,
        TasksModelRepository $tasksRepository
    ): Response {
        $task = new TasksModel();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Example: Check for duplicate task titles
            $existingTask = $tasksRepository->findOneBy(['title' => $task->getTitle()]);
            if ($existingTask) {
                $this->addFlash('error', 'A task with this title already exists.');
            } else {
                $entityManager->persist($task);
                $entityManager->flush();

                return $this->redirectToRoute('web_tasks');
            }
        }

        return $this->render('to_do_list/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}