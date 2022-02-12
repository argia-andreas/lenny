<?php

namespace App\Actions\Lenny;

use App\Actions\Linear\CreateLinearIssue;
use App\Console\Middleware\HasLinearTeamMiddleware;
use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearIssue;
use App\Traits\HasLinearMenus;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\CommandDecorator as Command;

class Todo
{
    use AsAction;
    use HasLinearMenus;

    public string $commandSignature = 'todo  {title?} {--title=} {--desc=} {--pr} {--checkout}';
    public string $commandDescription = 'Quick Linear Issue';

    public array $commandMiddleware = [
        HasLinearTeamMiddleware::class
    ];

    protected ?LinearIssue $createdIssue;

    public function handle(NewLinearIssueDto $issue): LinearIssue
    {
        return CreateLinearIssue::make()->handle($issue);
    }

    public function asCommand(Command $command): int
    {

        $issue = $this->askUserForNewIssue($command);

        $command->task(
            sprintf("Creating issue %s", $issue->title),
            fn() => $this->createdIssue = $this->handle($issue)
        );

        // Create PR
        if ($command->option('pr')) {
            $command->call(
                PullRequest::class,
                ['issue' => $this->createdIssue->identifier]
            );
        } elseif ($command->option('checkout')) {
            $command->call(
                Checkout::class,
                [$this->createdIssue->identifier]
            );
        }

        $this->printResult($command);

        return 0;
    }

    protected function askUserForNewIssue(Command $command): NewLinearIssueDto
    {
        try {
            if ($title = $command->argument('title')) {
                return new NewLinearIssueDto($title, config('linear.settings.teamId'));
            }

            $title = $command->option('title') ?: $command->ask('Issue Title');
            $desc = $command->option('desc') ?: $command->ask('Description', '');

            return new NewLinearIssueDto($title, config('linear.settings.teamId'), $desc);
        } catch (\Throwable $e) {
            $command->error('Issue not OK');
            exit();
        }
    }

    protected function printResult(Command $command): void
    {
        $command->newLine();
        $command->info(sprintf("Link to linear-issue: %s", $this->createdIssue->getUrl()));
        $command->newLine();
        $command->alert('Happy Coding!');
    }

}
