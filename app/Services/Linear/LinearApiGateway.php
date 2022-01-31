<?php

namespace App\Services\Linear;

use GraphQL\Client;
use UnhandledMatchError;

/**
 * @method Issue issues()
 * @method Issue issue()
 * @method Team teams()
 * @method Team team()
 * @method State states()
 * @method State state()
 * @method Cycle cycles()
 * @method Cycle cycle()
 * */
class LinearApiGateway
{
    protected Client $client;

    public function __construct(protected string $token)
    {
        $this->client = new Client(
            'https://api.linear.app/graphql',
            ['Authorization' => $this->token]
        );
    }

    public function api($service): AbstractLinear
    {
        return match ($service) {
            'issue', 'issues' => new Issue($this),
            'team', 'teams' => new Team($this),
            'state', 'states' => new State($this),
            'cycle', 'cycles' => new Cycle($this),
        };
    }

    /**
     * @param string $service method name
     * @param array $args arguments
     *
     * @return AbstractLinear
     *
     * @throws \Exception
     */
    public function __call(string $service, array $args)
    {
        try {
            return $this->api($service);
        } catch (UnhandledMatchError $e) {
            throw new \Exception(sprintf('Undefined method called: "%s"', $service));
        }
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
