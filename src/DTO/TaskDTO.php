<?php

namespace App\DTO;

use App\Entity\TasksModel;

class TaskDTO
{
    private int $id;
    private int $user_id;
    private string $title;
    private string $description;
    private string $due_date;
    private int $status;
    private int $priority;
    private int $event_id;

    public function __construct(TasksModel $model)
    {
        $this->id = $model->getId();
        $this->user_id = $model->getUserId();
        $this->title = $model->getTitle();
        $this->description = $model->getDescription();
        $due_date = $model->getDueDate();
        $due_date = $due_date ? $due_date->format('Y-m-d') : '';
        $this->due_date = $due_date;
        $this->status = $model->getStatus();
        $this->priority = $model->getPriority();
        $this->event_id = $model->getEventId() ?? 0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'priority' => $this->priority,
            'event_id' => $this->event_id,
        ];
    }
}