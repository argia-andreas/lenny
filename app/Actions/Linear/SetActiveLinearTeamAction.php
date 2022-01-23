<?php

namespace App\Actions\Linear;

use App\Services\Settings\Settings;

class SetActiveLinearTeamAction
{
    public function __construct(protected Settings $settings)
    {
    }

    public function execute($teamId)
    {
        $this->settings->set('teamId', $teamId);
        $this->settings->save();
    }
}
