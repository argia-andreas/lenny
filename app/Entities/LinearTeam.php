<?php

namespace App\Entities;

class LinearTeam
{
    public function __construct(
        public string $id,
        public string $name,
    )
    {
    }

    public static function fromRequest($data): self
    {
        return new self(
            ...collect($data)
            ->only(['id', 'name'])
            ->toArray()
        );
    }
}
