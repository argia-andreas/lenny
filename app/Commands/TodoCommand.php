<?php

namespace App\Commands;

use App\Actions\Linear\CreateLinearIssueAction;
use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearIssue;
use App\Traits\HasLinearMenus;
use LaravelZero\Framework\Commands\Command;

class TodoCommand extends Command
{
    use HasLinearMenus;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'todo {title?} {--title=} {--desc=} {--pr} {--checkout}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Quick Linear Issue';

    protected ?LinearIssue $createdIssue;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(CreateLinearIssueAction $createIssue)
    {
        if(! $this->ensureTeamIsSet()) {
            return -1;
        }

        $issue = $this->askUserForNewIssue();

        $this->task(
            sprintf("Creating issue %s", $issue->title),
            fn() => $this->createdIssue = $createIssue->execute($issue)
        );

        // Create PR
        if ($this->option('pr')) {
            $this->call(
                PullRequestCommand::class,
                ['issue' => $this->createdIssue->identifier]
            );
        } elseif ($this->option('checkout')) {
            $this->call(
                CheckoutCommand::class,
                [$this->createdIssue->identifier]
            );
        }

        $this->printResult();

        return 0;
    }

    protected function askUserForNewIssue(): NewLinearIssueDto
    {
        try {
            if ($title = $this->argument('title')) {
                return new NewLinearIssueDto($title, config('linear.settings.teamId'));
            }

            $title = $this->option('title') ?: $this->ask('Issue Title');
            $desc = $this->option('desc') ?: $this->ask('Description', '');

            return new NewLinearIssueDto($title, config('linear.settings.teamId'), $desc);
        } catch (\Throwable $e) {
            $this->error('Issue not OK');
            exit();
        }
    }

    protected function printResult(): void
    {
        $this->newLine();
        $this->info(sprintf("Link to linear-issue: %s", $this->createdIssue->getUrl()));
        $this->newLine();
        $this->alert('Happy Coding!');
    }
}
