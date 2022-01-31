<?php

namespace App\Services\Linear;

use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearIssue;
use GraphQL\Mutation;
use GraphQL\Query;
use GraphQL\QueryBuilder\QueryBuilder;
use GraphQL\RawObject;
use Illuminate\Support\Collection;

class Issue extends AbstractLinear
{
    public function create(NewLinearIssueDto $issue): LinearIssue
    {
        $mutation = (new Mutation('issueCreate'))
            ->setArguments([
                'input' => new RawObject('{
                    title: "' . $issue->title . '",
                    description: "' . $issue->description . '",
                    teamId: "' . $issue->teamId . '"
                }'),
            ])
            ->setSelectionSet([
                'success',
                (new Query('issue'))
                    ->setSelectionSet(self::fields()),
            ]);

        return $this->mutate($mutation)
            ->pipe(function ($issue) {
                throw_unless($issue['issueCreate']->success, \Exception::class, 'Issue not created');
                return LinearIssue::fromRequest($issue['issueCreate']->issue);
            });
    }

    public function find(string $identifier): LinearIssue
    {
        $gql = (new Query('issue'))
            ->setArguments(['id' => $identifier])
            ->setSelectionSet(self::fields());

        return $this->query($gql)
            ->pipe(function ($response) {
                return LinearIssue::fromRequest(data_get($response, 'issue'));
            });
    }

    public function all()
    {
        $teamId = config('linear.settings.teamId');
        $fields = self::relation();

        $gql = (new QueryBuilder('team'))
            ->setArgument('id', $teamId)
            ->selectField('id')
            ->selectField('name')
            ->selectField($fields);

        return $this->query($gql)
            ->dd();
    }

    public function triage()
    {
        $teamId = config('linear.settings.teamId');

        $gql = (new QueryBuilder('team'))
            ->setArgument('id', $teamId)
            ->selectField('id')
            ->selectField('name')
            ->selectField(
                (new Query('issues'))
                    ->setArguments([
                        'filter' => new RawObject('{
                          state: { type: { eq: "triage" } }
                          or: [
                              { snoozedUntilAt: { lt: "P0D" } },
                              { snoozedUntilAt: { null: true } }
                            ]
                        }'),
                    ])
                    ->setSelectionSet([
                        (new Query('nodes'))
                            ->setSelectionSet(self::fields()),
                    ])
            );

        return $this->query($gql)
            ->pipe(fn($response) => collect(data_get($response, 'team.issues.nodes', [])))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));
    }

    public function backlog()
    {
        $teamId = config('linear.settings.teamId');

        $gql = (new QueryBuilder('team'))
            ->setArgument('id', $teamId)
            ->selectField('id')
            ->selectField('name')
            ->selectField(
                (new Query('issues'))
                    ->setArguments([
                        'filter' => new RawObject('{
                          state: { type: { eq: "backlog" } }
                        }'),
                    ])
                    ->setSelectionSet([
                        (new Query('nodes'))
                            ->setSelectionSet(self::fields()),
                    ])
            );

        return $this->query($gql)
            ->pipe(fn($response) => collect(data_get($response, 'team.issues.nodes', [])))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));
    }

    /**
     * @return LinearIssue[]|Collection
     */
    public function activeCycle(): Collection
    {
        $teamId = config('linear.settings.teamId');

        $gql = (new QueryBuilder('team'))
            ->setArgument('id', $teamId)
            ->selectField('id')
            ->selectField('name')
            ->selectField(
                (new Query('activeCycle'))
                    ->setSelectionSet([
                        ...Cycle::fields(),
                        self::relation(),
                    ])
            );

        return $this->query($gql)
            ->pipe(fn($response) => collect(data_get($response, 'team.activeCycle.issues.nodes', [])))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));
    }

    public function cycle(string $cycleId)
    {
        $gql = (new Query('cycle'))
            ->setArguments(['id' => $cycleId])
            ->setSelectionSet([
                ...Cycle::fields(),
                self::relation(),
            ]);

        return $this->query($gql)
            ->pipe(fn($response) => collect(data_get($response, 'team.cycle.issues.nodes', [])))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));
    }

    public static function fields(): array
    {
        return [
            'id',
            'identifier',
            'title',
            'branchName',
            'number',
            'description',
            'createdAt',
            'archivedAt',
            'snoozedUntilAt',
            (new Query('assignee'))
                ->setSelectionSet([
                    'id',
                    'name',
                ]),
            (new Query('state'))
                ->setSelectionSet([
                    'id',
                    'name',
                ]),
            (new QueryBuilder('labels'))
                ->selectField(
                    (new Query('nodes'))
                        ->setSelectionSet([
                            'id',
                            'name',
                        ])
                )->getQuery(),

        ];
    }

    public static function relation(): Query|QueryBuilder
    {
        return (new QueryBuilder('issues'))
            ->selectField(
                (new Query('nodes'))
                    ->setSelectionSet(self::fields())
            )->getQuery();
    }
}
