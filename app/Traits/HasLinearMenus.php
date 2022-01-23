<?php

namespace App\Traits;

use App\Entities\LinearCycle;
use App\Services\Linear\Issue;
use App\Services\Linear\LinearApiGateway;

trait HasLinearMenus
{
    protected ?LinearApiGateway $linear;

    protected function linear()
    {
        if (!isset($this->linear)) {
            $this->linear = app(LinearApiGateway::class);
        }

        return $this->linear;
    }

    public function chooseCycle(): ?LinearCycle
    {
        $cycles = $this->linear()->cycles()->all();
        $cycles = $cycles
            ->sortByDesc('number')
            ->map(function (LinearCycle $cycle) {
                $cycle->name ??= 'Cycle ' . $cycle->number;

                if ($cycle->completedAt) {
                    $cycle->name .= ' (completed)';
                }

                if (now()->isAfter($cycle->startsAt) && now()->isBefore($cycle->endsAt)) {
                    $cycle->name .= ' (current)';
                }

                return $cycle;
            });

        $cycleId = $this->menu('Select an Cycle', $cycles->pluck('name', 'id')->toArray())->open();

        if (!$cycleId) {
            return null;
        }

        return $this->linear()->cycle()->with(Issue::class)->find($cycleId);
    }

    public function ensureTeamIsSet(): bool
    {
        if (!config('linear.settings.teamId')) {
            $this->error('Linear Team is no set. Please run `lenny team` to set it.');
            return false;
        }

        return true;
    }
}
