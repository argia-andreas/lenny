<?php

namespace App\Entities;

use Illuminate\Support\Collection;

class LinearIssue
{
    public function __construct(
        public string $id,
        public string $identifier,
        public string $title,
        public int $number,
        public string $branchName,
        public ?string $description = '',
        public ?string $state = '',
        public ?Collection $labels = null,
        public ?string $createdAt  = '',
        public ?string $archivedAt = '',
        public ?string $snoozedUntilAt = '',
    )
    {
        if(!$labels) {
            $this->labels = collect();
        }
    }

    public static function fromRequest($data): LinearIssue
    {
        $linearIssue =  new self(...collect($data)->only([
            'id',
            'identifier',
            'title',
            'number',
            'branchName',
            'description',
            'createdAt',
            'archivedAt',
            'snoozedUntilAt',
        ])->toArray());

        $linearIssue->state = $data['state']['name'] ?? 'unknown';

        $linearIssue->labels = collect(data_get($data, 'labels.nodes', []))
            ->map(fn($data) => LinearLabel::fromRequest($data));

        return $linearIssue;
    }

    public function getUrl(): string
    {
        return sprintf('https://linear.app/issue/%s', $this->identifier);
    }
}