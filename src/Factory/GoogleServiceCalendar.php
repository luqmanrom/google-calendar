<?php

namespace Geckob\GoogleCalendar\Factory;

use Google_Client;
use Google_Service_Calendar;


class GoogleServiceCalendar {


    public static function getInstance() {
        $client = new Google_Client();

        $credentials = $client->loadServiceAccountJson(
            config('geckob-google-calendar.client_secret_json'),
            'https://www.googleapis.com/auth/calendar'
        );

        $client->setAssertionCredentials($credentials);

        $service = new Google_Service_Calendar($client);

        return $service;

    }
}
