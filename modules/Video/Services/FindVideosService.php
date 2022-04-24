<?php

declare(strict_types=1);

namespace Video\Services;

use App\Repositories\VideoRepositoryInterface;
use Illuminate\Support\Collection;

class FindVideosService
{
    private $repo;

    public function __construct(VideoRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(): Collection
    {
        return $this->repo->findMany();
    }
}
