<?php

namespace App\Console\Middleware;

use App\Contracts\ConsoleMiddleware;
use App\Contracts\ConsoleRequestDTO;
use Closure;

class HasLinearTeamMiddleware implements ConsoleMiddleware
{
    public function handle($request, Closure $next): ConsoleRequestDTO
    {
        $this->hasTeam($request);

        return $next($request);
    }

    public function hasTeam($request)
    {
        if (!config('linear.settings.teamId')) {
            $request->command->call('team');

            $this->hasTeam($request);
        }

        return;
    }
}
