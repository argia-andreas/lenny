<?php

namespace App\Actions\Git;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitPushAction
{
    public function execute(string $branchName)
    {
        $process = new Process(['git', 'push', '--set-upstream', 'origin', $branchName]);
        $result = $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $result;
    }
}
