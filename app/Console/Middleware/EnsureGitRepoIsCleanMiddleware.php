<?php

namespace App\Console\Middleware;

use App\Actions\Git\GitRepoHasNonStagedChangesAction;
use App\Contracts\ConsoleMiddleware;
use App\Contracts\ConsoleRequestDTO;
use Closure;

class EnsureGitRepoIsCleanMiddleware implements ConsoleMiddleware
{
    public function handle($request, Closure $next): ConsoleRequestDTO
    {
        if (GitRepoHasNonStagedChangesAction::make()->handle()) {
            $request->command->error('Repo has unstaged changes!!');
            exit(-1);
        }

        return $next($request);
    }
}
