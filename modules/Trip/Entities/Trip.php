<?php

declare(strict_types=1);

namespace Trip\Entities;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Collection;

class Trip
{
    /**
     * The trip ID.
     *
     * @var null|int
     */
    private $id;

    /**
     * The user model instance.
     *
     * @var User
     */
    private $user;

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
     * The editors.
     *
     * @var Collection
     */
    private $editors;

    /**
     * The schedules of the trip.
     *
     * @var array|Schedule[]
     */
    private $schedules = [];

    /**
     * Determine the trip is collected or not.
     *
     * @var bool
     */
    private $is_collected;

    private $likes_count = 0;

    private $comments_count = 0;

    private $is_liked;

    private $is_published;

    private $updated_at;

    private $is_private;

    /**
     * Create a new entity instance.
     *
     * @param null|int $id
     * @param User     $user
     * @param string   $title
     * @param string   $start_at
     * @param string   $end_at
     * @param bool     $is_collected
     * @param bool     $is_liked
     * @param int      $is_published
     * @param int      $is_private
     * @param Carbon   $updated_at
     * @param $editors
     */
    public function __construct(
        ?int $id,
        User $user,
        string $title,
        string $start_at,
        string $end_at,
        int $is_published,
        int $is_private,
        Collection $editors,
        ?Carbon $updated_at = null,
        bool $is_collected = false,
        bool $is_liked = false
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->title = $title;
        $this->start_at = $start_at;
        $this->end_at = $end_at;
        $this->is_collected = $is_collected;
        $this->is_liked = $is_liked;
        $this->is_published = $is_published;
        $this->updated_at = $updated_at;
        $this->is_private = $is_private;
        $this->editors = $editors;
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
        return $this->user->id;
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
        return $this->editors->map(function($editor) {
            $user = $editor->user;
            if (! $user) {
                return;
            }
            return [
                'id' => $user->id,
                'name' => $user->name,
                'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
            ];
        })
            ->reject(function($editor) {
                return ! $editor;
            })
            ->values()
            ->toArray();
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
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'image_url' => $this->user->image_name ? env('AWS_URL') . $this->user->image_name : '',
            ],
            'is_collected' => $this->is_collected,
            'is_liked' => $this->is_liked,
            'is_private' => $this->is_private === 1 ? true : false,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'published_at' => $this->updated_at ? $this->updated_at->format('Y-m-d') : null,
            'editors' => $this->getEditors(),
        ];
    }

    /**
     * Transform to trip detail array.
     *
     * @return array
     */
    public function toDetailArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'image_url' => $this->user->image_name ? env('AWS_URL') . $this->user->image_name : '',
            ],
            'days' => $this->getDays(),
            'start_date' => $this->getStartAt()->format('Y-m-d'),
            'end_date' => $this->getEndAt()->format('Y-m-d'),
            'editors' => $this->editors,
            'is_collected' => $this->is_collected,
            'is_private' => $this->is_private === 1 ? true : false,
            'is_private' => $this->is_private,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'schedules' => $this->schedules,
            'editors' => $this->getEditors(),
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

    /**
     * Get the schedules of the trip.
     *
     * @return array|Schedule[]
     */
    public function getSchedules(): array
    {
        return $this->schedules;
    }

    /**
     * Set the schedules of the trip.
     *
     * @param array|Schedule[] $schedules the schedules of the trip
     *
     * @return self
     */
    public function setSchedules($schedules): self
    {
        $this->schedules = $schedules;

        return $this;
    }

    public function setLikesCount(int $likes_count): self
    {
        $this->likes_count = $likes_count;

        return $this;
    }

    public function setCommentsCount(int $comments_count): self
    {
        $this->comments_count = $comments_count;

        return $this;
    }

    public function getIsPublished(): bool
    {
        return $this->is_published === 1 ? true : false;
    }
}
