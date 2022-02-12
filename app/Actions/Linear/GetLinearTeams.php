<?php

namespace App\Actions\Linear;

use App\Entities\LinearTeam;
use App\Services\Linear\LinearApiGateway;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLinearTeams
{
    use AsAction;

    public function __construct(protected LinearApiGateway $apiGateway)
    {
    }

    /**
     * @return LinearTeam[]|Collection
     */
    public function handle(): Collection
    {
        return $this->apiGateway->teams()->all();
    }
}
