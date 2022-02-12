<?php

namespace App\Contracts;

use Closure;
use Illuminate\Console\Command;

interface ConsoleMiddleware
{
    public function handle(ConsoleRequestDTO $request, Closure $next): ConsoleRequestDTO;
}
