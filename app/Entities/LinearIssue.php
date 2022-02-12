<?php

namespace App\Entities;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LinearIssue
{
    public function __construct(
        public string      $id,
        public string      $identifier,
        public string      $title,
        public int         $number,
        public string      $branchName,
        public ?string     $description = '',
        public ?string     $state = '',
        public ?string     $stateType = '',
        public ?Collection $labels = null,
        public ?string     $createdAt = '',
        public ?string     $archivedAt = '',
        public ?string     $snoozedUntilAt = '',
    )
    {
        if (!$labels) {
            $this->labels = collect();
        }
    }

    public function completed(): bool
    {
        return $this->stateType === 'completed';
    }

    public static function fromRequest($data): LinearIssue
    {
        $linearIssue = new self(...collect($data)->only([
            'id',
            'identifier',
            'title',
            'number',
            'branchName',
            'createdAt',
            'archivedAt',
            'snoozedUntilAt',
        ])->toArray());

        $linearIssue->description = $data->description ?? $data->title;

        $linearIssue->state = $data->state->name ?? 'unknown';
        $linearIssue->stateType = $data->state->type ?? 'unknown';

        $linearIssue->labels = collect(data_get($data, 'labels.nodes', []))
            ->map(fn($data) => LinearLabel::fromRequest($data));

        return $linearIssue;
    }

    public function getUrl(): string
    {
        return sprintf('https://linear.app/issue/%s', $this->identifier);
    }

    public function title(): string
    {
        return (string)Str::of($this->description)
            ->whenContainsAll(
                ['<title>', '</title>'],
                fn($description) => $description->between('<title>', '</title>'),
                fn($description) => $this->title
            );
    }

    public function description(): string
    {
        return (string)Str::of($this->description)
            ->whenContainsAll(
                ['<description>', '</description>'],
                fn($description) => $description->between('<description>', '</description>'),
                fn($description) => $description
            );
    }
}
