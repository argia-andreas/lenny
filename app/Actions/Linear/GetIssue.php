<?php

namespace App\Actions\Linear;

use App\Entities\LinearIssue;
use App\Services\Linear\LinearApiGateway;
use Lorisleiva\Actions\Concerns\AsAction;

class GetIssue
{
    use AsAction;

    public function __construct(protected LinearApiGateway $api)
    {
    }

    public function handle(string $issueId): ?LinearIssue
    {
        return $this->api
            ->issue()
            ->find($issueId);
    }
}
