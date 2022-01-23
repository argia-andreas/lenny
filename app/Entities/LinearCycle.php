<?php

namespace App\Entities;

use Illuminate\Support\Collection;

class LinearCycle
{
    public function __construct(
        public string      $id,
        public int         $number,
        public ?string     $name = null,
        public ?string     $completedAt = null,
        public ?string     $endsAt = null,
        public ?float      $progress = null,
        public ?string     $startsAt = null,
        public ?Collection $issues = null,
    )
    {
        if($issues === null) {
            $this->issues = collect();
        }
    }

    public static function fromRequest($data)
    {
        $cycle = new self(
            ...collect($data)->only([
            'id',
            'name',
            'number',
            'completedAt',
            'endsAt',
            'progress',
            'startsAt',
        ])
            ->toArray()
        );

        $cycle->issues = collect(data_get($data, 'issues.nodes', []))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));

        return $cycle;
    }
}
