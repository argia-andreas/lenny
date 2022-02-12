<?php

namespace App\Providers;

use App\Services\CheckRequirements;
use App\Services\Settings\LoadSettings;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen(CommandStarting::class, CheckRequirements::class);
        Event::listen(CommandStarting::class, LoadSettings::class);
    }
}
