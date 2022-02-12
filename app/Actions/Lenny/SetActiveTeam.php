<?php

namespace App\Actions\Lenny;

use App\Actions\Linear\GetLinearTeams;
use App\Services\Settings\Settings;
use App\Traits\HasLinearMenus;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\CommandDecorator as Command;

class SetActiveTeam
{
    use AsAction;
    use HasLinearMenus;

    public string $commandSignature = 'team {teamId?}';
    public string $commandDescription = 'Set the active Linear team';

    public function __construct(protected Settings $settings)
    {
    }

    public function handle(string $teamId): void
    {
        $this->settings->set('teamId', $teamId);
        $this->settings->save();
    }

    public function asCommand(Command $command): void
    {
        $teams = GetLinearTeams::run();

        $selectedTeam = $this->selectTeam($command,$teams);

        if (!$selectedTeam) {
            $command->error('No team selected');
            return;
        }

        $command->task(
            sprintf("Setting active team to %s", $selectedTeam->name),
            fn() => $this->handle($selectedTeam->id)
        );
    }
}
