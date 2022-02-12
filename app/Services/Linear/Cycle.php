<?php

namespace App\Services\Linear;

use App\Entities\LinearCycle;
use GraphQL\Query;
use GraphQL\QueryBuilder\QueryBuilder;
use GraphQL\RawObject;

class Cycle extends AbstractLinear
{

    public function active(): ?LinearCycle
    {
        $teamId = config('linear.settings.teamId');

        $gql = (new QueryBuilder('team'))
            ->setArgument('id', $teamId)
            ->selectField(
                (new Query('activeCycle'))
                    ->setSelectionSet([
                        ...self::fields(),
                        ...$this->getWith(),
                    ])
            );

        return $this->query($gql)
            ->pipe(fn($response) => LinearCycle::fromRequest(
                data_get($response, 'team.activeCycle', [])
            ));
    }

    public function all()
    {
        $teamId = config('linear.settings.teamId');

        $gql = (new QueryBuilder('team'))
            ->setArgument('id', $teamId)
            ->selectField('id')
            ->selectField('name')
            ->selectField(
                (new QueryBuilder('cycles'))
                    ->setArgument('filter', new RawObject('{endsAt: { gt: "-P1M" }}'))
                    ->selectField(
                        (new Query('nodes'))
                            ->setSelectionSet([
                                ...self::fields(),
                                ...$this->getWith(),
                            ])
                    )
            );

        return $this->query($gql)
            ->pipe(fn($result) => collect(data_get($result, 'team.cycles.nodes', [])))
            ->map(fn($cycle) => LinearCycle::fromRequest($cycle));
    }

    public function find($cycleId)
    {
        $gql = (new Query('cycle'))
            ->setArguments(['id' => $cycleId])
            ->setSelectionSet([
                ...self::fields(),
                ...$this->getWith(),
            ]);

        return $this->query($gql)
            ->pipe(fn($response) => LinearCycle::fromRequest(data_get($response, 'cycle', [])));
    }

    public static function fields(): array
    {
        return [
            'id',
            'number',
            'name',
            'completedAt',
            'endsAt',
            'progress',
            'startsAt',
        ];
    }
}
