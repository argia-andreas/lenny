<?php

namespace App\Formatters\Changelog;
use App\Entities\LinearCycle;
use App\Entities\LinearIssue;
use Carbon\Carbon;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use function collect;

class MarkdownChangelogFormatter implements ChangelogFormatter
{

    public function __construct(protected Command $command)
    {
    }

    public function format(LinearCycle $cycle): void
    {
        $title = sprintf(
            '# :rocket: Sprint - %s',
            $cycle->name ?? 'Cycle ' . $cycle->number,
        );

        $this->command->line($title);
        $this->command->line("---");
        $this->command->line(
            sprintf(
                'Date: %s - %s',
                Carbon::parse($cycle->startsAt)->toDateString(),
                        Carbon::parse($cycle->endsAt)->toDateString(),
            ));
        $this->command->line(sprintf('Progress: %d',$cycle->progress));
        $this->command->newLine();
        $this->command->line("---");
        $this->command->newLine();
        $this->command->line("## Highlights");
        $this->highlights($cycle);
        $this->command->newLine();
        $this->command->line("---");
        $this->command->newLine();
        $this->command->line("## Tasks");
        $this->tasks($cycle);
        $this->command->newLine();
    }

    public function highlights(LinearCycle $cycle)
    {
        $cycle
            ->issues
            ->filter(fn (LinearIssue $issue) => $issue->labels->contains('name', 'highlight'))
            ->each(function(LinearIssue $issue) {
               $this->command->line(sprintf('### %s', $issue->title));
               $description = Str::of($issue->description)->between('<highlight>', '</highlight>');
                $this->command->line($description);
            });
    }

    public function tasks(LinearCycle $cycle)
    {
        $cycle
            ->issues
            ->filter(fn (LinearIssue $issue) => ! $issue->labels->contains('name', 'highlight'))
            ->filter(fn (LinearIssue $issue) => $issue->state == 'Done')
            ->map(fn(LinearIssue $issue) => "- " . $issue->title)
            ->each(fn(string $cardName) => $this->command->line($cardName));
    }
}
