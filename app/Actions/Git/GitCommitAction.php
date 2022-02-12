<?php

namespace App\Actions\Git;

use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitCommitAction
{
    use AsAction;

    public function handle(string $commitMessage = 'wip')
    {
        $process = new Process(['git', 'commit', '-m', $commitMessage, '--allow-empty']);
        $result = $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $result;
    }
}
