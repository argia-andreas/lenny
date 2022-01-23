<?php

namespace App\Actions\Linear;

use App\Entities\LinearTeam;
use App\Services\Linear\LinearApiGateway;
use Illuminate\Support\Collection;

class GetLinearTeamsAction
{
    public function __construct(protected LinearApiGateway $apiGateway)
    {
    }

    /**
     * @return LinearTeam[]|Collection
     */
    public function execute(): Collection
    {
        return $this->apiGateway->teams()->all();
    }
}
