<?php

namespace App\Actions\Git;

use App\DataTransferObjects\PullRequestDto;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitPullRequestAction
{
    public function __construct(
        protected GitPullRequestCommentAction $createPullRequestComment,
    )
    {
    }

    public function execute(PullRequestDto $pullRequestDto): string|null
    {
        $createPullRequest = tap(
            new Process(['gh', 'pr', 'create', "--title={$pullRequestDto->name}", "--body={$pullRequestDto->body}"]),
            function ($createPullRequest) {
                $createPullRequest->run();
                throw_unless($createPullRequest->isSuccessful(), ProcessFailedException::class, $createPullRequest);
            }
        );

        return $createPullRequest->getOutput();
    }
}
