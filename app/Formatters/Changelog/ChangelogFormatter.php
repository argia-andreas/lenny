<?php

namespace App\Formatters\Changelog;

use App\Entities\LinearCycle;
use LaravelZero\Framework\Commands\Command;

interface ChangelogFormatter
{
    public function format(LinearCycle $cycle): string;
}
