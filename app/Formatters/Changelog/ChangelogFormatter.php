<?php

namespace App\Formatters\Changelog;

use App\Entities\LinearCycle;
use LaravelZero\Framework\Commands\Command;

interface ChangelogFormatter
{
    public function __construct(Command $command);

    public function format(LinearCycle $cycle): void;
}
