<?php

namespace App\Services\Settings;

use App\Exceptions\InvalidSettingsException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Settings
{
    protected array $settings = [];
    public function __construct()
    {
    }

    public function load()
    {
        try {
            $this->settings = json_decode(File::get(base_dir() . 'settings.json'), true);
            Config::set('linear.settings', $this->settings);
        } catch (\Throwable $e) {
            throw InvalidSettingsException::create();
        }
    }

    public function get($key)
    {
        return Config::get("linear.settings.{$key}");
    }

    public function set(string $key, $value)
    {
        throw_unless(
            Str::of($key)->startsWith(['teamId','quick.', 'changelog.', 'pr.']),
            InvalidSettingsException::class,
            'Wrong type',
        );

        Config::set("linear.settings.{$key}", $value);
    }

    public function save()
    {
        $data = config('linear.settings');

        File::put(
            base_dir() . 'settings.json',
            json_encode($data),
        );
    }
}
