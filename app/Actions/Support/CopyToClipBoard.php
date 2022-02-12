<?php

namespace App\Actions\Support;

use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CopyToClipBoard
{
    use AsAction;

    public function handle(string $text): int
    {

        if(! config('linear.hasClipboardSupport')) {
            return -1;
        }

        $process = new Process(['pbcopy']);
        $process->setInput($text);
        $result = $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $result;
    }
}
