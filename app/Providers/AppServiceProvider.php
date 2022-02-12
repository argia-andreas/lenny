<?php

namespace App\Providers;

use App\Exceptions\InvalidAuthException;
use App\Services\CommandDecorator;
use App\Services\Linear\LinearApiGateway;
use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Decorators\CommandDecorator as BaseCommandDecorator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind(BaseCommandDecorator::class, CommandDecorator::class);

        try {
            $this->app->bind(LinearApiGateway::class, function ($app) {
                return new LinearApiGateway(
                    config('linear.auth.token'),
                );
            });
        } catch (\Throwable $e) {
            throw new InvalidAuthException($e->getMessage());
        }

    }
}
