<?php

declare(strict_types=1);

namespace Video\Services;

use App\Repositories\VideoRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateVideoService
{
    private $repo;

    public function __construct(VideoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $user_id, UploadedFile $video)
    {
        $video_name = Str::random(8) . time() . 'MOV';

        $response = Storage::disk('s3')->put($video_name, $video, [
            'visibility' => 'public',
        ]);

        if ($response) {
            $this->repo->create($user_id, $video_name);
        }
    }
}
