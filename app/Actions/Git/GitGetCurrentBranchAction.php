<?php

namespace App\Actions\Git;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitGetCurrentBranchAction
{
    use AsAction;

    public function handle(): string
    {
        $output = tap(
            (new Process(['git', 'branch', '--show-current'])),
            function (Process $process) {
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
            }
        )->getOutput();

        return Str::of($output)->trim();
    }
}
