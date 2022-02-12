<?php

namespace App\Console\Middleware;

use App\Actions\Git\CheckGitRepoExistsAction;
use App\Contracts\ConsoleMiddleware;
use App\Contracts\ConsoleRequestDTO;
use Closure;

class HasGitRepoMiddleware implements ConsoleMiddleware
{
    public function handle($request, Closure $next): ConsoleRequestDTO
    {
        if (!CheckGitRepoExistsAction::make()->handle(getcwd())) {
            $request->command->error('Git repo does not exist in current directory.');
            exit(-1);
        }

        return $next($request);
    }
}
