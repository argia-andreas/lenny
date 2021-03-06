<?php

namespace App\Actions\Git;

use App\DataTransferObjects\GitPullRequestCommentDto;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitPullRequestCommentAction
{
    use AsAction;

    public function handle(GitPullRequestCommentDto $prComment)
    {
        $process = new Process(['gh', 'pr', 'comment', "--body={$prComment->body}"]);
        $result = $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
//            $this->newLine();
//            $this->line($output);

        return $result;

    }
}
