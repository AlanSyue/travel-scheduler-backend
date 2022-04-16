<?php

declare(strict_types=1);

namespace Trip\Transformer;

use Trip\Entities\Trip;

class TripDetailTransformer
{
    public function transform(Trip $trip): array
    {
        return [
            'data' => $trip->toDetailArray(),
        ];
    }
}
