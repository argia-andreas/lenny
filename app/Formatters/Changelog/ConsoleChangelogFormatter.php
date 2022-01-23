<?php

namespace App\Formatters\Changelog;
use App\Entities\LinearCycle;
use App\Entities\LinearIssue;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

class ConsoleChangelogFormatter implements ChangelogFormatter
{

    public function __construct(protected Command $command)
    {
    }


    public function format(LinearCycle $cycle): void
    {

        $title = sprintf(
            'Sprint - %s',
            $cycle->name ?? 'Cycle ' . $cycle->number,
        );

        $this->command->title($title);
        $this->command->table(['Tasks'], $this->tasks($cycle));
    }

    public function tasks(LinearCycle $cycle): Collection
    {
        return $cycle
            ->issues
            ->filter(fn (LinearIssue $issue) => $issue->state == 'Done')
            ->map(fn(LinearIssue $issue) => ["- " . $issue->title]);
    }

}
