<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $event_date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $event_time = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEventDate(): ?\DateTime
    {
        return $this->event_date;
    }

    public function setEventDate(?\DateTime $event_date): static
    {
        $this->event_date = $event_date;

        return $this;
    }

    public function getEventTime(): ?\DateTime
    {
        return $this->event_time;
    }

    public function setEventTime(?\DateTime $event_time): static
    {
        $this->event_time = $event_time;

        return $this;
    }
}
