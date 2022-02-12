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

        if($this->confirm('Update linear token?', false)) {
            $this->token = $this->secret('Linear API Token:');
            $this->task('Saving Linear API token', [$this, 'saveLinearApiToken']);
        }

        if($this->confirm('Re-publish template stubs?', false)) {
            $this->task('Publishing template stub', [$this, 'publishTemplateStub']);
        }

        return 0;
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

            if (!File::isDirectory(base_dir() . 'cache/')) {
                File::makeDirectory(base_dir() . 'cache/');
            }

            // Auth.
            File::put($authFileName, $authData);

            // Settings
            File::put(base_dir() . "settings.json", json_encode([
                'teamId' => null,
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

    public function publishTemplateStub()
    {
        $templateFile = base_dir() . 'changelog.blade.php';

        $this->newLine();
        $this->line('Saving template stub to: ' . $templateFile);

        $template = <<<'EOT'
        # 🚀 Sprint - {{ $cycleName }}
        ---
        Date: {{ $startsAt }} - {{ $endsAt }}
        Progress: {{ $progress }}%
        @if($highlights)
        ---
        ## Highlights
        @foreach($highlights as $issue)
        ### {{ $issue->title }}{{ $issue->completed ? '- Done' : '' }}
        {{ $issue->description }}
        @endforeach
        @endif
        @if($features)
        ---
        ## Funktioner
        @foreach($features as $issue)
        - [{{ $issue->completed ? 'X' : ' ' }}] {{ $issue->title }}
        @endforeach
        @endif
        @if($bugs)
        ---
        ## Buggar
        @foreach($bugs as $issue)
        - [{{ $issue->completed ? 'X' : ' ' }}] {{ $issue->title }}
        @endforeach
        @endif
        @if($tasks)
        ---
        ## Övrigt
        @foreach($tasks as $issue)
        - [{{ $issue->completed ? 'X' : ' ' }}] {{ $issue->title }}
        @endforeach
        @endif
        EOT;

        File::put($templateFile, $template);

        $templateFile = base_dir() . 'cycle.blade.php';

        $this->newLine();
        $this->line('Saving template stub to: ' . $templateFile);
        $this->newLine();

        $template = <<<'EOT'
        # 🚀 Cycle - {{ $cycleName }}
        ---
        Date: {{ $startsAt }} - {{ $endsAt }}
        Progress: {{ $progress }}%
        @if($highlights)
        ---
        ## Highlights
        @foreach($highlights as $issue)
        ### {{ $issue->title }}
        {{ $issue->description }}
        @endforeach
        @endif
        @if($features)
        ---
        ## Funktioner
        @foreach($features as $issue)
        - [{{ $issue->completed ? 'X' : ' ' }}] {{ $issue->title }}
        @endforeach
        @endif
        @if($bugs)
        ---
        ## Buggar
        @foreach($bugs as $issue)
        - [{{ $issue->completed ? 'X' : ' ' }}] {{ $issue->title }}
        @endforeach
        @endif
        @if($tasks)
        ---
        ## Övrigt
        @foreach($tasks as $issue)
        - [{{ $issue->completed ? 'X' : ' ' }}] {{ $issue->title }}
        @endforeach
        @endif
        EOT;

        File::put($templateFile, $template);
    }
}
