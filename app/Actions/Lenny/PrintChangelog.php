<?php

namespace App\Actions\Lenny;

use App\Actions\Linear\GetActiveCycle;
use App\Console\Middleware\HasLinearTeamMiddleware;
use App\Entities\LinearCycle;
use App\Formatters\Changelog\ChangelogFormatter;
use App\Formatters\Changelog\MarkdownChangelogFormatter;
use App\Traits\HasLinearMenus;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\CommandDecorator as Command;

class PrintChangelog
{
    use AsAction;
    use HasLinearMenus;

    public string $commandSignature = 'changelog {-A|--active-cycle}';
    public string $commandDescription = 'Print the changelog';

    public array $commandMiddleware = [
        HasLinearTeamMiddleware::class,
    ];

    public function handle(LinearCycle $cycle, ChangelogFormatter $formatter = null): string
    {
        $formatter ??= new MarkdownChangelogFormatter();
        return $formatter->format($cycle);
    }

    public function asCommand(Command $command): void
    {
        if ($command->option('active-cycle')) {
            $cycle = GetActiveCycle::make()->handle();
        } else {
            $cycle = $this->chooseCycle($command);
        }

        if (!$cycle) {
            $command->info('No cycle selected...');
            return;
        }

        $output = $this->handle($cycle);

        Str::of($output)
            ->explode(PHP_EOL)
            ->each(fn($line) => $command->line($line));
    }
}