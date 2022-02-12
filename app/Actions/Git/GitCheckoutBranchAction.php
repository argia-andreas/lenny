<?php

namespace App\Actions\Git;

use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitCheckoutBranchAction
{
    use AsAction;

    public function __construct(
        protected GitCommitAction $gitCommitAction,
        protected GitPushAction   $gitPushAction,
    )
    {
    }

    public function handle(string $branchName): int
    {
        $process = new Process(['git', 'checkout', '-b', $branchName]);
        $result = $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->gitCommitAction->handle('Started working');
        $this->gitPushAction->handle($branchName);

        return $result;

    }
}
