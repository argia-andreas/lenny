<?php

namespace App\Actions\Linear;

use App\Services\Linear\LinearApiGateway;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAllCycles
{
    use AsAction;

    public function __construct(protected LinearApiGateway $api)
    {
    }


    public function handle(): Collection
    {
        return $this->api
            ->cycles()
            ->all();
    }
}
