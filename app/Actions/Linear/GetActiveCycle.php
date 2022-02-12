<?php

namespace App\Actions\Linear;

use App\Entities\LinearCycle;
use App\Services\Linear\Issue;
use App\Services\Linear\LinearApiGateway;
use Lorisleiva\Actions\Concerns\AsAction;

class GetActiveCycle
{
    use AsAction;

    public function __construct(protected LinearApiGateway $api)
    {
    }

    public function handle(): ?LinearCycle
    {
        return $this->api
            ->cycle()
            ->with(Issue::class)
            ->active();
    }
}
