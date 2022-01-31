<?php

namespace App\Services\Linear;

use App\Services\Linear\LinearApiGateway as Client;
use GraphQL\Query;
use GraphQL\QueryBuilder\QueryBuilderInterface;
use Illuminate\Support\Collection;

class AbstractLinear
{
    protected array $relations = [];

    public function __construct(protected Client $client)
    {
    }

    protected function query(Query|QueryBuilderInterface $gql): Collection
    {
        return collect(
            $this
                ->client
                ->getClient()
                ->runQuery($gql)
                ->getData()
        );
    }

    protected function mutate(Query|QueryBuilderInterface $gql): Collection
    {
        return collect(
            $this
                ->client
                ->getClient()
                ->runQuery($gql)
                ->getData()
        );
    }

    /**
     * @param string $relation
     * @return $this
     */
    public function with(string $relation): self
    {
        $self = clone $this;

        if (!array_key_exists($relation, array_flip($self->relations))) {
            $self->relations[] = $relation;
        }

        return $self;
    }

    public function getWith(): array
    {
        return collect($this->relations)
            ->map(fn($relation) => $relation::relation())
            ->toArray();
    }
}
