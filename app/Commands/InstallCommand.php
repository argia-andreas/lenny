<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Auth Linear';

    protected ?string $key;
    protected ?string $token;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->title('Enter Linear API token');
        $this->info('Go to https://linear.app/ - Account settings and set up a personal API token.');
        $this->line('Good Luck!');
        $this->newLine();
        $this->token = $this->secret('Linear API Token:');

        $this->task('Saving Linear API token', [$this, 'saveLinearApiToken']);
    }

    public function saveLinearApiToken()
    {
        try {
            $authData = json_encode([
                'token' => $this->token,
            ]);

            $authFileName = base_dir() . config('app.auth.filename');

            $this->line('Saving config to: ' . $authFileName);

            if (!File::isDirectory(base_dir())) {
                File::makeDirectory(base_dir());
            }

            // Auth.
            File::put($authFileName, $authData);

            // Settings
            File::put(base_dir() . "settings.json", json_encode([
                'pr' => [
                    'lastBoardId' => null,
                    'lastListId' => null,
                ],
                'changelog' => [
                    'lastBoardId' => null,
                    'lastListId' => null,
                ],
                'quick' => [
                    'lastBoardId' => null,
                    'lastListId' => null,
                ],
            ]));

            return true;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return false;
        }
    }
}
