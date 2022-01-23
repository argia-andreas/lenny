<?php

namespace App\Commands;

use App\Actions\Linear\GetLinearTeamsAction;
use App\Actions\Linear\SetActiveLinearTeamAction;
use LaravelZero\Framework\Commands\Command;

class SetTeamCommand extends Command
{

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'team';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Set active Linear team';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(GetLinearTeamsAction $getLinearTeamsAction, SetActiveLinearTeamAction $setActiveLinearTeamAction)
    {
        $teams = $getLinearTeamsAction->execute();

        $selectedTeam = $this->selectTeam($teams);

        if (!$selectedTeam) {
            $this->error('No team selected');
            return -1;
        }

        $this->task(
            sprintf("Setting active team to %s", $selectedTeam->name),
            fn() => $setActiveLinearTeamAction->execute($selectedTeam->id)
        );

        return 0;
    }

    protected function selectTeam($teams)
    {
        $selectedTeamId = $this
            ->menu(
                'Select Active Team',
                $teams->pluck('name', 'id')->toArray()
            )
            ->open();

        return $teams->firstWhere('id', $selectedTeamId);
    }
}
