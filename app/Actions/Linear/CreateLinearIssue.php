<?php

namespace App\Actions\Linear;

use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearIssue;
use App\Services\Linear\LinearApiGateway;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateLinearIssue
{
    use AsAction;

    public function __construct(protected LinearApiGateway $apiGateway)
    {
    }

    public function handle(NewLinearIssueDto $issue): LinearIssue
    {
        return $this->apiGateway->issue()->create($issue);
    }
}
