<?php

namespace App\Services\Linear;

use GraphQL\QueryBuilder\QueryBuilder;

class State extends AbstractLinear
{

    public function all()
    {
        $teamId = config('linear.settings.teamId');
        $gql =
                (new QueryBuilder('workflowStates'))
                    ->selectField(
                        (new QueryBuilder('nodes'))
                            ->selectField('id')
                            ->selectField('name')
                            ->selectField('type')
                    );

        return $this->query($gql)
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
