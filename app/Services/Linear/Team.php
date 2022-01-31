<?php

namespace App\Services\Linear;

use App\Entities\LinearTeam;
use GraphQL\QueryBuilder\QueryBuilder;
use Illuminate\Support\Collection;

class Team extends AbstractLinear
{
    /**
     * @return Team[]|Collection
     */
    public function all(): Collection
    {
        $gqlBuilder = (new QueryBuilder('teams'))
            ->selectField(
                (new QueryBuilder('nodes'))
                    ->selectField('id')
                    ->selectField('name')
            );

        return $this->query($gqlBuilder)
            ->pipe(fn($response) => collect(data_get($response, 'teams.nodes', [])))
            ->map(fn($team) => LinearTeam::fromRequest($team));
    }

}
