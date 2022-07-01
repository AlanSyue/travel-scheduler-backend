<?php

declare(strict_types=1);

namespace Video\Services;

use App\Models\VideoRating;
use App\Repositories\BlockRepositoryInterface;
use App\Repositories\VideoRatingRepositoryInterface;

class FindVideoRatingService
{
    private $block_repo;

    private $video_rating_repo;

    public function __construct(
        BlockRepositoryInterface $block_repo,
        VideoRatingRepositoryInterface $video_rating_repo
    ) {
        $this->block_repo = $block_repo;
        $this->video_rating_repo = $video_rating_repo;
    }

    public function execute(int $video_id, ?int $user_id): array
    {
        $block_user_ids = $user_id ? $this->block_repo->findByUserId($user_id)->pluck('block_user_id')->toArray() : [];

        $video_ratings = $this->video_rating_repo->findByVideoId($video_id)
            ->reject(function (VideoRating $video_rating) use ($block_user_ids) {
                return in_array($video_rating->user_id, $block_user_ids);
            })
            ->values();

        $total = $video_ratings->count();

        $type_count = $video_ratings->countBy(function (VideoRating $video_rating) {
            return $video_rating->type;
        });

        $user_like = $video_ratings->map(function (VideoRating $video_rating) {
            $user = $video_rating->user;

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image_url' => $user->image_name ? env('AWS_URL') . $user->image_name : '',
                ],
                'type' => $video_rating->type,
            ];
        });

        return [
            'summarize' => [
                'total' => $total,
                'detail' => $type_count->toArray(),
            ],
            'list' => $user_like->toArray(),
        ];
    }
}
