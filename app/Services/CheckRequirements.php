<?php

namespace App\Services;

use App\Exceptions\InvalidAuthException;
use App\Exceptions\InvalidSettingsException;
use App\Exceptions\MissingCommandsException;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CheckRequirements
{
    public function handle(CommandStarting $event)
    {
        if (
            ! in_array($event->command, [
                null,
                'install',
                'app:build',
            ])
        ) {
            $this->checkRequirements();
        }
    }

    public function checkRequirements()
    {
        try {
            File::get(base_dir() . config('app.auth.filename'));
        } catch (\Throwable $e) {
            throw InvalidAuthException::create();
        }

        try {
            File::get(base_dir() . 'settings.json');
        } catch (\Exception $e) {
            throw InvalidSettingsException::create();
        }

        try {
            $hasGit = tap(new Process(['which', 'git']), fn($p) => $p->run());

            if (!$hasGit->isSuccessful()) {
                throw new ProcessFailedException($hasGit);
            }

            $hasGh = tap(new Process(['which', 'gh']), fn($p) => $p->run());

            if (!$hasGh->isSuccessful()) {
                throw new ProcessFailedException($hasGh);
            }

        } catch (\Throwable $e) {
            throw MissingCommandsException::create();
        }
    }

}
