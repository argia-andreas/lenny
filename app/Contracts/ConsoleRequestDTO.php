<?php

namespace App\Contracts;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Console\Events\CommandStarting;

class ConsoleRequestDTO
{
    public function __construct(
        public Command $command,
    )
    {
    }
}
