<?php

namespace App\Console\Middleware;

use App\Actions\Git\GitGetCurrentBranchAction;
use App\Contracts\ConsoleMiddleware;
use App\Contracts\ConsoleRequestDTO;
use Closure;
use Illuminate\Support\Str;

class EnsureGitBranchMiddleware implements ConsoleMiddleware
{
    public function handle($request, Closure $next): ConsoleRequestDTO
    {

        $currentBranch = GitGetCurrentBranchAction::make()->handle();

        if(Str::of($currentBranch)->contains(['main', 'master'])) {
            return $next($request);
        }

        if (! $request->command->confirm(sprintf("Sure you want to proceed? Current branch is not main or master, it's %s", $currentBranch))) {
            $request->command->error('Exiting!');
            exit(-1);
        }

        return $next($request);
    }
}
