<?php

namespace App\Services\Linear;

use App\DataTransferObjects\NewLinearIssueDto;
use App\Entities\LinearIssue;
use Illuminate\Support\Collection;

class Issue extends AbstractLinear
{
    public function create(NewLinearIssueDto $issue): LinearIssue
    {
        $mutation = <<<GQL
            mutation IssueCreate {
              issueCreate(
                input: {
                  title: "{$issue->title}"
                  description: "{$issue->description}"
                  teamId: "{$issue->teamId}"
                }
              ) {
                success
                issue {
                  id
                  identifier
                  title
                  branchName
                  number
                  description
                }
              }
            }
        GQL;

        return $this->mutate($mutation)
            ->pipe(function ($issue) {
                throw_unless($issue['issueCreate']['success'], \Exception::class, 'Issue not created');
                return LinearIssue::fromRequest($issue['issueCreate']['issue']);
            });
    }

    public function find(string $identifier): LinearIssue
    {
        $query = <<<GQL
            query Issue {
                  issue(id: "{$identifier}") {
                    id
                    identifier
                    title
                    branchName
                    number
                    description
                  }
                }
        GQL;

        return $this->query($query)
            ->pipe(function ($response) {
                return new LinearIssue(...$response['issue']);
            });
    }

    public function all()
    {
        $teamId = config('linear.settings.teamId');
        $fields = self::fields();
        $query = <<<GQL
            query Team {
                  team(id: "{$teamId}") {
                    id
                    name
                    {$fields}
                  }
                }
        GQL;

        return $this->query($query)
            ->dd();
    }

    public function triage()
    {
        $teamId = config('linear.settings.teamId');
        $query = <<<GQL
            query Team {
                  team(id: "$teamId") {
                    id
                    name
                    issues(
                        filter: {
                          state: { type: { eq: "triage" } }
                          or: [
                              { snoozedUntilAt: { lt: "P0D" } },
                              { snoozedUntilAt: { null: true } }
                            ]
                        }
                    ) {
                      nodes {
                        id
                        identifier
                        title
                        branchName
                        number
                        description
                        labels {
                          nodes {
                            id
                            name
                          }
                        }
                        state {
                          id
                          name
                        }
                        assignee {
                          id
                          name
                        }
                        createdAt
                        archivedAt
                        snoozedUntilAt
                      }
                    }
                  }
                }
        GQL;

        return $this->query($query)
            ->pipe(fn($response) => collect(data_get($response, 'team.issues.nodes', [])))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));
    }

    public function backlog()
    {
        $teamId = config('linear.settings.teamId');
        $query = <<<GQL
            query Team {
                  team(id: "$teamId") {
                    id
                    name
                    issues(
                        filter: {
                          state: { type: { eq: "backlog" } }
                        }
                    ) {
                      nodes {
                        id
                        identifier
                        title
                        branchName
                        number
                        description
                        labels {
                          nodes {
                            id
                            name
                          }
                        }
                        state {
                          id
                          name
                        }
                        assignee {
                          id
                          name
                        }
                        createdAt
                        archivedAt
                        snoozedUntilAt
                      }
                    }
                  }
                }
        GQL;

        return $this->query($query)
            ->pipe(fn($response) => collect(data_get($response, 'team.issues.nodes', [])))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));
    }

    /**
     * @return LinearIssue[]|Collection
     */
    public function activeCycle(): Collection
    {
        $teamId = config('linear.settings.teamId');
        $fields = self::fields();
        $query = <<<GQL
            query Team {
                  team(id: "{$teamId}") {
                    id
                    name
                    activeCycle {
                        id
                        name
                        number
                        {$fields}
                    }
                  }
                }
        GQL;

        return $this->query($query)
            ->pipe(fn($response) => collect(data_get($response, 'team.activeCycle.issues.nodes', [])))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));

    }

    public function cycle(string $cycleId)
    {
        $teamId = config('linear.settings.teamId');
        $fields = self::fields();
        $query = <<<GQL
            query Cycles {
                    cycle(id: "{$cycleId}") {
                        id
                        name
                        number
                        {$fields}
                    }
                }
        GQL;

        return $this->query($query)
            ->pipe(fn($response) => collect(data_get($response, 'team.cycle.issues.nodes', [])))
            ->map(fn($issue) => LinearIssue::fromRequest($issue));
    }

    public static function fields(): string
    {
        return <<<GQL
            issues {
              nodes {
                id
                identifier
                title
                branchName
                number
                description
                labels {
                  nodes {
                    id
                    name
                  }
                }
                state {
                  id
                  name
                }
                assignee {
                  id
                  name
                }
                createdAt
                archivedAt
                snoozedUntilAt
              }
            }
        GQL;
    }
}
