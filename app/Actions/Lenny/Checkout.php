<?php

namespace App\Actions\Lenny;

use App\Actions\Git\GitCheckoutBranchAction;
use App\Console\Middleware\EnsureGitBranchMiddleware;
use App\Console\Middleware\EnsureGitRepoIsCleanMiddleware;
use App\Console\Middleware\HasGitRepoMiddleware;
use App\Console\Middleware\HasLinearTeamMiddleware;
use App\Entities\LinearIssue;
use App\Traits\HasLinearMenus;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\CommandDecorator as Command;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Checkout
{
    use AsAction;
    use HasLinearMenus;

    public string $commandSignature = 'checkout {issue?}';
    public string $commandDescription = 'Checkout Linear Issue';

    public array $commandMiddleware = [
        HasLinearTeamMiddleware::class,
        HasGitRepoMiddleware::class,
        EnsureGitRepoIsCleanMiddleware::class,
        EnsureGitBranchMiddleware::class,
    ];

    public function __construct(protected GitCheckoutBranchAction $gitCheckoutBranchAction)
    {
    }


    public function handle(LinearIssue $issue): int
    {
        return $this->gitCheckoutBranchAction->handle($issue->branchName);
    }

    public function asCommand(Command $command): int
    {

        $issue = $this->getIssue($command);

        if(! $issue) {
            $command->error('No issue found!');
            return 0;
        }

        if(! $command->confirm(sprintf("Checkout %s", $issue->branchName))) {
            $command->info('exiting..');
            return 0;
        }

        try {
            $command->task(
                "Checkout branch $issue->branchName",
                fn() => $this->handle($issue)
            );

            $command->alert('Happy coding!');
        } catch (ProcessFailedException $e) {
            $command->error($e->getMessage());
        }

        return 0;
    }
}
