<?php

namespace App\Traits;

use App\Actions\Linear\GetAllCycles;
use App\Actions\Linear\GetCycle;
use App\Entities\LinearCycle;
use App\Entities\LinearIssue;
use App\Services\Linear\Issue;
use App\Services\Linear\LinearApiGateway;
use Lorisleiva\Actions\Decorators\CommandDecorator as Command;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;

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

    public function chooseCycle(Command $command): ?LinearCycle
    {
        $cycles = GetAllCycles::make()->handle();
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

        $cycleId = $command->menu('Select an Cycle', $cycles->pluck('name', 'id')->toArray())->open();

        if (!$cycleId) {
            return null;
        }

        return GetCycle::make()->handle($cycleId);
    }

    protected function selectTeam(Command $command, $teams)
    {
        $selectedTeamId = $command
            ->menu(
                'Select Active Team',
                $teams->pluck('name', 'id')->toArray()
            )
            ->open();

        return $teams->firstWhere('id', $selectedTeamId);
    }

    public function getIssue(Command $command): ?LinearIssue
    {
        if($identifier = $command->argument('issue')) {
            return app(LinearApiGateway::class)->issue()->find($identifier);
        }

        $activeCycle = app(LinearApiGateway::class)->cycle()->with(Issue::class)->active();
        if(!$activeCycle) {
            $backlog = app(LinearApiGateway::class)->issues()->backlog();
            $unstartedIssues = app(LinearApiGateway::class)->issues()->unstarted();
            $issues = $backlog->merge($unstartedIssues);
        } else {
            $issues = $activeCycle->issues;
        }

        $triageIssues = $this->linear()->issues()->triage();

        $issues = $issues->merge($triageIssues);
        $states = $issues->groupBy('state');

        $menu = $command->menu("Select an issue for PR");
        $states->each(
            fn($stateIssues, $title) => $menu->addSubMenu(
                $title,
                $this->subMenu($title, $stateIssues, $menu)
            )
        );

        return $menu->open();
    }


    public function subMenu($title, $children, $menu): callable
    {
        return function (CliMenuBuilder $b) use ($title, $children, $menu) {
            $b->disableDefaultItems()
                ->setTitle($title);

            collect($children)->each(function($child) use($b, $menu) {
                $b->addItem($child->title, function(CliMenu $cliMenu) use ($menu, $child) {
                    $menu->setResult($child);
                    $cliMenu->close();
                });
            });

            $b->addItem('..', new GoBackAction); //add a go back button
        };
    }

}
