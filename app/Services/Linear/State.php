<?php

namespace App\Services\Linear;

use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearIssue;

class State extends AbstractLinear
{

    public function all()
    {
        $query = <<<GQL
            query {
              workflowStates {
                    nodes {
                      id
                      name
                    }
                  }
            }
        GQL;

        return $this->query($query)
            ->dd();
    }

    public function find($stateId)
    {
        $teamId = config('linear.settings.teamId');
        $query = <<<GQL
            query {
                  workflowState(id: "{$stateId}}") {
                        issues {
                          nodes {
                            title
                          }
                        }
                      }
                }
        GQL;

        return $this->query($query)
            ->dd();
    }

}
