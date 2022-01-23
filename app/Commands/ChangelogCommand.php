<?php

namespace App\Commands;

use App\Formatters\Changelog\ConsoleChangelogFormatter;
use App\Formatters\Changelog\ChangelogFormatter;
use App\Formatters\Changelog\MarkdownChangelogFormatter;
use App\Services\Linear\Issue;
use App\Services\Linear\LinearApiGateway;
use App\Traits\HasLinearMenus;
use LaravelZero\Framework\Commands\Command;

class ChangelogCommand extends Command
{
    use HasLinearMenus;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'changelog {--C|--console} {--A|--active-cycle} {--L|--links}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get changelog for a Linear Cycle';

    public function handle(LinearApiGateway $apiGateway): int
    {
        if(! $this->ensureTeamIsSet()) {
            return -1;
        }

        if ($this->option('active-cycle')) {
            $cycle = $apiGateway->cycle()->with(Issue::class)->active();
        } else {
            $cycle = $this->chooseCycle();
        }

        if (!$cycle) {
            $this->info('No cycle selected...');
            return 1;
        }

        $this->getFormatter()
            ->format($cycle);

        return 0;
    }

    public function getFormatter(): ChangelogFormatter
    {
        $format = $this->option('console') ? 'console' : 'markdown';

        return match ($format) {
            'console' => new ConsoleChangelogFormatter($this),
            default => new MarkdownChangelogFormatter($this),
        };
    }
}
