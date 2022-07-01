<?php

declare(strict_types=1);

namespace Trip\Entities;

class Schedule
{
    /**
     * Get the schedule ID.
     *
     * @var null|int
     */
    private $id;

    /**
     * Get the trip ID.
     *
     * @var int
     */
    private $trip_id;

    /**
     * The schedule type.
     *
     * @var string
     */
    private $type;

    /**
     * Get the schedule day.
     *
     * @var int
     */
    private $day;

    /**
     * Get the schedule name.
     *
     * @var string
     */
    private $name;

    /**
     * Get the address.
     *
     * @var string
     */
    private $address;

    /**
     * Get the schedule start time.
     *
     * @var string
     */
    private $start_time;

    /**
     * Get the schedule duration.
     *
     * @var float
     */
    private $duration;

    /**
     * Get the schedule traffic time.
     *
     * @var float
     */
    private $traffic_time;

    /**
     * Get the location latitude.
     *
     * @var float
     */
    private $lat;

    /**
     * Get the location longitude.
     *
     * @var float
     */
    private $long;

    private $description;

    private $images;

    /**
     * Create a new entity instance.
     *
     * @param null|int $id
     * @param int      $trip_id
     * @param string   $type
     * @param int      $day
     * @param string   $name
     * @param string   $address
     * @param string   $start_time
     * @param float    $duration
     * @param float    $traffic_time
     * @param float    $lat
     * @param float    $long
     * @param string   $description
     * @param array    $images
     */
    public function __construct(
        ?int $id,
        int $trip_id,
        string $type,
        int $day,
        string $name,
        string $address,
        string $start_time,
        float $duration,
        float $traffic_time,
        float $lat,
        float $long,
        string $description,
        ?array $images = []
    ) {
        $this->id = $id;
        $this->trip_id = $trip_id;
        $this->type = $type;
        $this->day = $day;
        $this->name = $name;
        $this->address = $address;
        $this->start_time = $start_time;
        $this->duration = $duration;
        $this->traffic_time = $traffic_time;
        $this->lat = $lat;
        $this->long = $long;
        $this->description = $description;
        $this->images = $images;
    }

    /**
     * Get get the schedule ID.
     *
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get get the trip ID.
     *
     * @return int
     */
    public function getTripId(): int
    {
        return $this->trip_id;
    }

    /**
     * Set get the trip ID.
     *
     * @param int $trip_id get the trip ID
     *
     * @return self
     */
    public function setTripId(int $trip_id): self
    {
        $this->trip_id = $trip_id;

        return $this;
    }

    /**
     * Get get the schedule name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set get the schedule name.
     *
     * @param string $name get the schedule name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get get the address.
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Set get the address.
     *
     * @param string $address get the address
     *
     * @return self
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get get the schedule start time.
     *
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->start_time;
    }

    /**
     * Set get the schedule start time.
     *
     * @param string $start_time get the schedule start time
     *
     * @return self
     */
    public function setStartTime(string $start_time): self
    {
        $this->start_time = $start_time;

        return $this;
    }

    /**
     * Get get the schedule duration.
     *
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * Set get the schedule duration.
     *
     * @param float $duration get the schedule duration
     *
     * @return self
     */
    public function setDuration(float $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get get the schedule traffic time.
     *
     * @return float
     */
    public function getTrafficTime(): float
    {
        return $this->traffic_time;
    }

    /**
     * Set get the schedule traffic time.
     *
     * @param float $traffic_time get the schedule traffic time
     *
     * @return self
     */
    public function setTrafficTime(float $traffic_time): self
    {
        $this->traffic_time = $traffic_time;

        return $this;
    }

    /**
     * Get get the location latitude.
     *
     * @return float
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * Set get the location latitude.
     *
     * @param float $lat get the location latitude
     *
     * @return self
     */
    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get get the location longitude.
     *
     * @return float
     */
    public function getLong(): float
    {
        return $this->long;
    }

    /**
     * Set get the location longitude.
     *
     * @param float $long get the location longitude
     *
     * @return self
     */
    public function setLong(float $long): self
    {
        $this->long = $long;

        return $this;
    }

    /**
     * Transform to the array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'trip_id' => $this->trip_id,
            'type' => $this->type,
            'day' => $this->day,
            'address' => $this->address,
            'start_time' => $this->start_time,
            'duration' => $this->duration,
            'traffic_time' => $this->traffic_time,
            'lat' => $this->lat,
            'long' => $this->long,
            'description' => $this->description,
        ];
    }

    /**
     * Transform to the detail array.
     *
     * @return array
     */
    public function toDetailArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'trip_id' => $this->trip_id,
            'type' => $this->type,
            'day' => $this->day,
            'address' => $this->address,
            'start_time' => $this->start_time,
            'duration' => $this->duration,
            'traffic_time' => $this->traffic_time,
            'position' => [
                'lat' => $this->lat,
                'long' => $this->long,
            ],
            'description' => $this->description,
            'images' => $this->images,
        ];
    }

    /**
     * Get get the schedule day.
     *
     * @return int
     */
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * Set get the schedule day.
     *
     * @param int $day get the schedule day
     *
     * @return self
     */
    public function setDay(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get the schedule type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the schedule type.
     *
     * @param string $type the schedule type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
