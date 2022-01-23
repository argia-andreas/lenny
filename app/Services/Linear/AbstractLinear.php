<?php

namespace App\Services\Linear;

use App\Services\Linear\LinearApiGateway as Client;
use Illuminate\Support\Collection;

class AbstractLinear
{
    protected array $relations = [];

    public function __construct(protected Client $client)
    {
    }

    protected function query(string $data): Collection
    {
        return Collection::wrap(
            $this->client
                ->getClient()
                ->post('', [
                    'query' => $data,
                ])
                ->throw()
                ->json()
        )
            ->pipe(fn($data) => collect($data['data'] ?? []));
    }

    protected function mutate(string $data): Collection
    {
        return Collection::wrap(
            $this->client
                ->getClient()
                ->post('', [
                    'query' => $data,
                ])
                ->throw()
                ->json()
        )
            ->pipe(fn($data) => collect($data['data'] ?? []));
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

    public function getWith()
    {
        return collect($this->relations)
            ->map(fn($relation) => $relation::fields())
            ->join(PHP_EOL);
    }
}
