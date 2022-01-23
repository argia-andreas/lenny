<?php

namespace App\Services\Linear;

use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearCycle;
use App\Entities\LinearIssue;

class Cycle extends AbstractLinear
{

    public function active(): LinearCycle
    {
        $teamId = config('linear.settings.teamId');

        $query = <<<GQL
            query Team {
                  team(id: "{$teamId}") {
                    activeCycle {
                        id
                        name
                        number
                        {$this->getWith()}
                    }
                  }
                }
        GQL;

        return $this->query($query)
            ->pipe(fn($response) => LinearCycle::fromRequest(
                data_get($response, 'team.activeCycle', [])
            ));
    }

    public function all()
    {
        $teamId = config('linear.settings.teamId');
        $query = <<<GQL
            query Team {
                  team(id: "{$teamId}") {
                    id
                    name
                    cycles(filter: {
                        endsAt: { gt: "-P1M" }
                      }) {
                            nodes {
                              id
                              number
                              name
                              completedAt
                              endsAt
                              progress
                              startsAt
                            }
                          }
                  }
                }
        GQL;

        return $this->query($query)
            ->pipe(fn($result) => collect(data_get($result, 'team.cycles.nodes', [])))
            ->map(fn($cycle) => LinearCycle::fromRequest($cycle));
    }

    public function find($cycleId)
    {
        $teamId = config('linear.settings.teamId');
        $issues = Issue::fields();

        $query = <<<GQL
            query Cycles{
                  cycle(id: "{$cycleId}") {
                        id
                        number
                        name
                        completedAt
                        endsAt
                        progress
                        startsAt
                        {$issues}
                      }
                }
        GQL;

        return $this->query($query)
            ->pipe(fn($response) => LinearCycle::fromRequest(data_get($response, 'cycle', [])));
    }

}
