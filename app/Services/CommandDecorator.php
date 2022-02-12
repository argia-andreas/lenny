<?php

namespace App\Services;
use App\Contracts\ConsoleRequestDTO;
use Closure;
use Lorisleiva\Actions\Decorators\CommandDecorator as BaseCommandDecorator;

class CommandDecorator extends BaseCommandDecorator
{
    public array $middleware = [];

    public function __construct($action)
    {
        parent::__construct($action);
        $this->middleware = $this->fromActionMethodOrProperty('getCommandMiddleware', 'commandMiddleware') ?? [];
    }

    public function handle()
    {
        $this->next()(new ConsoleRequestDTO($this));
        parent::handle();
    }

    protected function next(): Closure
    {
        return function (ConsoleRequestDTO $command): ConsoleRequestDTO {
            $nextMiddleware = array_shift($this->middleware);

            return $nextMiddleware
                ? (new $nextMiddleware)->handle($command, $this->next())
                : $command;
        };
    }
}
