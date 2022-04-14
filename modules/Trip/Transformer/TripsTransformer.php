<?php

declare(strict_types=1);

namespace Trip\Transformer;

use Illuminate\Support\Collection;

class TripsTransformer
{
    /**
     * Transform the trips data.
     *
     * @param Collection $trips
     *
     * @return array
     */
    public function transform(Collection $trips): array
    {
        return ['data' => $trips->toArray()];
    }
}
