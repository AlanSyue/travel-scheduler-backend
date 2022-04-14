<?php

declare(strict_types=1);

namespace Trip\Entities;

use Carbon\Carbon;

class Trip
{
    /**
     * The trip ID.
     *
     * @var null|int
     */
    private $id;

    /**
     * The user ID.
     *
     * @var int
     */
    private $user_id;

    /**
     * The trip title.
     *
     * @var string
     */
    private $title;

    /**
     * The trip start at.
     *
     * @var string
     */
    private $start_at;

    /**
     * The trip end at.
     *
     * @var string
     */
    private $end_at;

    /**
     * The trip total days.
     *
     * @var int
     */
    private $days;

    /**
     * The editors.
     *
     * @var array
     */
    private $editors = [];

    /**
     * Create a new entity instance.
     *
     * @param null|int $id
     * @param int      $user_id
     * @param string   $title
     * @param string   $start_at
     * @param string   $end_at
     */
    public function __construct(?int $id, int $user_id, string $title, string $start_at, string $end_at)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->title = $title;
        $this->start_at = $start_at;
        $this->end_at = $end_at;
    }

    /**
     * Get the value of id.
     *
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the value of id.
     *
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the user ID.
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Set the user ID.
     *
     * @param int $user_id
     *
     * @return self
     */
    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get the value of title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the value of title.
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of days.
     *
     * @return int
     */
    public function getDays()
    {
        $start_time = Carbon::parse($this->start_at);
        $end_time = Carbon::parse($this->end_at);

        // include the start date
        return $start_time->diffInDays($end_time) + 1;
    }

    /**
     * Get the value of editors.
     *
     * @return array
     */
    public function getEditors(): array
    {
        return $this->editors;
    }

    /**
     * Set the value of editors
     *
     * @param array $editors
     *
     * @return self
     */
    public function setEditors(array $editors)
    {
        $this->editors = $editors;

        return $this;
    }

    /**
     * Transform the data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'days' => $this->getDays(),
            'editors' => $this->editors,
        ];
    }

    /**
     * Get the trip start at.
     *
     * @return Carbon
     */
    public function getStartAt(): Carbon
    {
        return Carbon::parse($this->start_at);
    }

    /**
     * Set the trip start at.
     *
     * @param string $start_at the trip start at
     *
     * @return self
     */
    public function setStartAt(string $start_at): self
    {
        $this->start_at = $start_at;

        return $this;
    }

    /**
     * Get the trip end at.
     *
     * @return Carbon
     */
    public function getEndAt(): Carbon
    {
        return Carbon::parse($this->end_at);
    }

    /**
     * Set the trip end at.
     *
     * @param string $end_at the trip end at
     *
     * @return self
     */
    public function setEndAt(string $end_at): self
    {
        $this->end_at = $end_at;

        return $this;
    }
}
