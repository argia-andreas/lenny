<?php

namespace App\Actions\Linear;

use App\Services\Linear\LinearApiGateway;
use Illuminate\Support\Str;

class GetBranchNameFromLinearIssueAction
{
    public function __construct(protected LinearApiGateway $apiGateway)
    {
    }

    public function execute(string $identifier): string
    {
        $issue = $this->apiGateway->issue()->find($identifier);
        return $issue->branchName;
    }
}
