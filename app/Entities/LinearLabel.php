<?php

namespace App\Entities;

use Illuminate\Support\Collection;

class LinearLabel
{
    public function __construct(
        public string $id,
        public string $name,
    )
    {
    }

    public static function fromRequest($data): LinearLabel
    {
        return  new self(...collect($data)->only([
            'id',
            'name',
        ])->toArray());
    }
}
