<?php

namespace Geckob\GoogleCalendar;

use Illuminate\Support\ServiceProvider;


class GoogleCalendarServiceProvider extends ServiceProvider
{

    public function boot() {
        $this->publishes([
            __DIR__.'/../config/geckob-google-calendar.php' => config_path('geckob-google-calendar.php'),
        ], 'config');
    }

    public function register() {

    }

}