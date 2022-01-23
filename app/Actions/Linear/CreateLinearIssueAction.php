<?php

namespace App\Actions\Linear;

use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearIssue;
use App\Services\Linear\LinearApiGateway;

class CreateLinearIssueAction
{
    public function __construct(protected LinearApiGateway $apiGateway)
    {
    }

    public function execute(NewLinearIssueDto $issue): LinearIssue
    {
        return $this->apiGateway->issue()->create($issue);
    }
}
