<?php

namespace App\Actions\Git;

use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckGitRepoExistsAction
{
    use AsAction;

    public function handle($directory = null): bool
    {
        $directory ??= getcwd();
        return File::isDirectory(sprintf("%s/.git", $directory));
    }
}
