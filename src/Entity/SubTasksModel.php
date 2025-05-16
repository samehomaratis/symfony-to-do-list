<?php

namespace App\Entity;

use App\Repository\SubTasksModelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'subtasks ')]
#[ORM\Entity(repositoryClass: SubTasksModelRepository::class)]
class SubTasksModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $task_id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?bool $is_completed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskId(): ?int
    {
        return $this->task_id;
    }

    public function setTaskId(int $task_id): static
    {
        $this->task_id = $task_id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->is_completed;
    }

    public function setIsCompleted(bool $is_completed): static
    {
        $this->is_completed = $is_completed;

        return $this;
    }
}
