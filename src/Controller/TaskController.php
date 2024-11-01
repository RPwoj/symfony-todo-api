<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TaskRepository;
use App\Entity\Task;
use App\Repository\GroupTaskRepository;
use App\Entity\GroupTask;

class TaskController extends AbstractController
{
    public $res = [];

    #[Route('/tasks/', methods: ['GET'])]
    public function showAll(TaskRepository $taskRepository): JsonResponse
    {
        $tasks = $taskRepository->findAll();

        foreach ($tasks as $task) {
            $this->res[$task->getId()] = $task->getName();
        }

        return new JsonResponse($this->res);
    }

    #[Route('/task/{taskID}', methods: ['GET'])]
    public function show(TaskRepository $taskRepository, int $taskID): JsonResponse
    {
        $task = $taskRepository->find($taskID);
        $this->res['task_name'] = $task->getName();

        return new JsonResponse($this->res);
    }

    #[Route('/task/', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, GroupTaskRepository $groupTaskRepository, Request $request): JsonResponse
    {
        $name = $request->query->get('name');
        $groupID = $request->query->get('group_id');

        if ($name && $groupID) {
            $group = $groupTaskRepository->find($groupID);
            
            $task = new Task();
            $task->setName($name);
            $task->setTaskGroup($group);
            
            $entityManager->persist($task);
            $entityManager->flush();
            
            $this->res['info'] = 'Saved new task with id '.$task->getId();
            
        } else {
            $this->res['error'] = 'too few data';
        }

        return new JsonResponse($this->res);
    }

    #[Route('/task/{taskID}', methods: ['PATCH'])]
    public function edit(EntityManagerInterface $entityManager, Request $request, TaskRepository $taskRepository, int $taskID): Response
    {
        $newName = $request->query->get('new_name');

        if ($taskID && $newName) {
            $task = $taskRepository->find($taskID);

            $task->setName($newName);
            $entityManager->persist($task);
            $entityManager->flush();

            $this->res['info'] = "Task with id {$taskID} has new name: {$newName}";

        } else {
            $this->res['error'] = 'too few data';
        }

        return new JsonResponse($this->res);
    }

    #[Route('/task/{taskID}', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, TaskRepository $taskRepository, int $taskID): Response
    {
        if ($taskID) {
            $task = $taskRepository->find($taskID);

            $entityManager->remove($task);
            $entityManager->flush();

            $this->res['info'] = "task with id {$taskID} deleted!";
        
        } else {
            $this->res['error'] = 'too few data';
        }

        return new JsonResponse($this->res);
    }
}
