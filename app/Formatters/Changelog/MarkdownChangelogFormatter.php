<?php

namespace App\Formatters\Changelog;

use App\Entities\LinearCycle;
use App\Entities\LinearIssue;
use Carbon\Carbon;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class MarkdownChangelogFormatter implements ChangelogFormatter
{
    public function format(LinearCycle $cycle): string
    {
        return view('changelog')
            ->with('cycleName', $this->title($cycle))
            ->with('startsAt', Carbon::parse($cycle->startsAt)->format('Y-m-d'))
            ->with('endsAt', Carbon::parse($cycle->endsAt)->format('Y-m-d'))
            ->with('progress', round($cycle->progress * 100, 0))
            ->with('highlights', $this->highlights($cycle))
            ->with('features', $this->features($cycle))
            ->with('bugs', $this->bugs($cycle))
            ->with('tasks', $this->tasks($cycle))
            ->render();
    }

    public function title($cycle): string
    {
        return $cycle->name ?? 'Cycle ' . $cycle->number;
    }

    public function highlights($cycle): array
    {
        return $cycle
            ->issues
            ->filter(fn(LinearIssue $issue) => $issue->stateType == 'completed')
            ->filter(fn(LinearIssue $issue) => $issue->labels->contains('name', 'highlight'))
            ->map(fn (LinearIssue $issue) => (object) [
                'title' => $issue->title(),
                'description' => $issue->description(),
                'completed' => $issue->completed(),
            ])
            ->toArray();
    }

    public function features($cycle): array
    {
        return $cycle
            ->issues
            ->filter(fn(LinearIssue $issue) => $issue->stateType == 'completed')
            ->filter(fn(LinearIssue $issue) => $issue->labels->contains('name', 'feature'))
            ->map(fn (LinearIssue $issue) => (object) [
                'title' => $issue->title(),
                'description' => $issue->description(),
                'completed' => $issue->completed(),
            ])
            ->toArray();
    }

    public function bugs($cycle): array
    {
        return $cycle
            ->issues
            ->filter(fn(LinearIssue $issue) => $issue->stateType == 'completed')
            ->filter(fn(LinearIssue $issue) => $issue->labels->contains('name', 'bug'))
            ->map(fn (LinearIssue $issue) => (object) [
                'title' => $issue->title(),
                'description' => $issue->description(),
                'completed' => $issue->completed(),
            ])
            ->toArray();
    }

    public function tasks($cycle): array
    {
        return $cycle
            ->issues
            ->reject(fn(LinearIssue $issue) => $issue->labels->contains('name', 'highlight'))
            ->reject(fn(LinearIssue $issue) => $issue->labels->contains('name', 'feature'))
            ->reject(fn(LinearIssue $issue) => $issue->labels->contains('name', 'bug'))
            ->filter(fn(LinearIssue $issue) => $issue->stateType == 'completed')
            ->map(fn (LinearIssue $issue) => (object) [
                'title' => $issue->title(),
                'description' => $issue->description(),
                'completed' => $issue->completed(),
            ])
            ->toArray();
    }
}
