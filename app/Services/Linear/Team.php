<?php

namespace App\Services\Linear;

use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearIssue;
use App\Entities\LinearTeam;
use Illuminate\Support\Collection;

class Team extends AbstractLinear
{
    /**
     * @return Team[]|Collection
     */
    public function all(): Collection
    {
        $query = <<<GQL
            query Teams {
              teams {
                nodes {
                  id
                  name
                }
              }
            }
        GQL;

        return $this->query($query)
            ->pipe(fn($response) => collect(data_get($response, 'teams.nodes', [])))
            ->map(fn($team) => LinearTeam::fromRequest($team));
    }

}
