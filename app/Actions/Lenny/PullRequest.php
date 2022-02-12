<?php

namespace App\Actions\Lenny;

use App\Actions\Git\GitCheckoutBranchAction as CheckoutBranch;
use App\Actions\Git\GitPullRequestAction;
use App\Console\Middleware\EnsureGitBranchMiddleware;
use App\Console\Middleware\EnsureGitRepoIsCleanMiddleware;
use App\Console\Middleware\HasGitRepoMiddleware;
use App\Console\Middleware\HasLinearTeamMiddleware;
use App\DataTransferObjects\PullRequestDto;
use App\Traits\HasLinearMenus;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\CommandDecorator as Command;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PullRequest
{
    use AsAction;
    use HasLinearMenus;

    public string $commandSignature = 'pr {issue?}';
    public string $commandDescription = 'Create a Pull Request.';

    protected ?string $pullRequestUrl;

    public array $commandMiddleware = [
        HasLinearTeamMiddleware::class,
        HasGitRepoMiddleware::class,
        EnsureGitRepoIsCleanMiddleware::class,
        EnsureGitBranchMiddleware::class,
    ];

    public function __construct(protected CheckoutBranch $checkoutBranch, protected GitPullRequestAction $pullRequest)
    {
    }


    public function handle(PullRequestDto $pr): string
    {
        return $this->pullRequest->handle($pr);
    }

    public function asCommand(Command $command): int
    {

        $issue = $this->getIssue($command);

        if(! $issue) {
            $command->error('No issue found!');
            return 0;
        }

        $pullRequestDto = new PullRequestDto(
            sprintf('%s - %s',$issue->identifier, $issue->title),
            $issue->description ?? $issue->title
        );

        if (!$command->confirm(sprintf("Checkout and create PR for: %s", $issue->branchName))) {
            $command->info('exiting..');
            return 0;
        }

        try {
            $command->task(
                sprintf("Checkout branch %s", $issue->branchName),
                fn() => $this->checkoutBranch->handle($issue->branchName)
            );

            $command->task(
                "Create Pull Request",
                fn() => $this->pullRequestUrl = $this->handle($pullRequestDto)
            );

            $command->info(sprintf("PullRequest url: %s", $this->pullRequestUrl));
            $command->alert('Happy coding!');
        } catch (ProcessFailedException $e) {
            $command->error($e->getMessage());
        }

        return 0;
    }
}
