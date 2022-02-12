<?php

namespace App\Actions\Linear;

use App\Entities\LinearCycle;
use App\Services\Linear\Issue;
use App\Services\Linear\LinearApiGateway;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCycle
{
    use AsAction;

    public function __construct(protected LinearApiGateway $api)
    {
    }

    public function handle(string $cycleId): ?LinearCycle
    {
        return $this->api
            ->cycle()
            ->with(Issue::class)
            ->find($cycleId);
    }
}
