<?php

namespace App\Actions\Git;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitRepoHasNonStagedChangesAction
{
    use AsAction;

    public function __construct(protected CheckGitRepoExistsAction $checkGitRepoExistsAction)
    {
    }

    public function handle(): string
    {
        throw_unless(
            $this->checkGitRepoExistsAction->handle(),
            new \Exception('Git repo does not exist')
        );

        $output = tap(
            (new Process(['git', 'status', '--porcelain'])),
            function (Process $process) {
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
            }
        )->getOutput();

        return $this->hasNonStagedChanges($output);
    }

    public function hasNonStagedChanges($output): bool
    {
        return Str::of($output)
            ->trim()
            ->explode(PHP_EOL)
            ->filter()
            ->map(fn($line) => Str::of($line))
            ->reject(fn ($line) => $line->startsWith('??'))
            ->reject(fn ($line) => $line->substr(1, 1) == ' ')
            ->isNotEmpty();
    }
}
