<?php

namespace App\DTO;

use App\Entity\Events;

class EventDTO
{
    private int $id;
    private string $name;
    private string $event_date;
    private string $event_time;

    public function __construct(Events $event)
    {
        $this->id = $event->getId();
        $this->name = $event->getName();
        $event_date = $event->getEventDate();
        $event_date = $event_date ? $event_date->format('Y-m-d') : '';
        $this->event_date = $event_date;

        $event_time = $event->getEventTime();
        $event_time = $event_time ? $event_time->format('H:i') : '';
        $this->event_time = $event_time;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'event_date' => $this->event_date,
            'event_time' => $this->event_time,
        ];
    }
}