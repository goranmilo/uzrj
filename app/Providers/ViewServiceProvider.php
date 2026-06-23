<?php

namespace App\Providers;

use App\View\Components\ThemeCss;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the theme CSS component
        Blade::component('theme-css', ThemeCss::class);
    }
}
