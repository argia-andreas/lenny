<?php

namespace App\Services\Settings;

use App\Exceptions\InvalidAuthException;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class LoadSettings
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
            $this->loadSettings();
        }
    }

    protected function loadSettings()
    {
        try {
            $auth = json_decode(File::get(base_dir() . config('app.auth.filename')), true);
            Config::set('linear.auth', $auth);
        } catch (\Throwable $e) {
            throw InvalidAuthException::create();
        }

        $settings = app(Settings::class);
        $settings->load();

        $hasClipBoardSupport = tap(new Process(['which', 'pbcopy']), fn($p) => $p->run());

        if ($hasClipBoardSupport->isSuccessful()) {
            Config::set('linear.hasClipboardSupport', true);
        }
    }

}
