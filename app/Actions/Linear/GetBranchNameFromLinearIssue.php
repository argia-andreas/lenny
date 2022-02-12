<?php

namespace App\Actions\Linear;

use App\Services\Linear\LinearApiGateway;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class GetBranchNameFromLinearIssue
{
    use AsAction;

    public function __construct(protected LinearApiGateway $apiGateway)
    {
    }

    public function handle(string $identifier): string
    {
        $issue = $this->apiGateway->issue()->find($identifier);
        return $issue->branchName;
    }
}
