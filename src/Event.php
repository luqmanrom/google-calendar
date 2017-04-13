<?php

namespace Geckob\GoogleCalendar;

use Carbon\Carbon;
use Geckob\GoogleCalendar\Factory\GoogleServiceCalendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use DateTime;


class Event
{

    public $googleEvent;

    protected $googleCalendar;

    protected $calendarId;


    public function __construct($googleEvent = null,$calendarId = null)
    {
        $calendarId = $this->getCalendarId($calendarId);

        if (func_num_args() >= 1) {

            $this->googleEvent = $googleEvent;

            $this->calendarId = $calendarId;

        } else {

            $service = GoogleServiceCalendar::getInstance();

            $this->googleEvent = new Google_Service_Calendar_Event();

            $this->googleCalendar = new GoogleCalendar($service, $calendarId);
        }

    }

    protected function getCalendarId($calendarId = null) {

        if ($calendarId instanceof GoogleCalendar) {

            $this->googleCalendar = $calendarId;

            $calendarId = $calendarId->getCalendarId();
        } else {
            $config = config('geckob-google-calendar');

            $calendarId = is_null($calendarId)? array_get($config, 'calendar_id') : $calendarId;
        }

        return $calendarId;

    }


    public function __set($name, $value) {

        // Translate to match Google Library
        $name = $this->getFieldName($name);

        if (in_array($name, ['start.date', 'end.date', 'start.dateTime', 'end.dateTime'])) {
            $this->setDateProperty($name, $value);

        } else {
            $this->googleEvent->$name = $value;
        }

    }

    public function __get($name) {
        $name = $this->getFieldName($name);

        $value = array_get($this->googleEvent, $name);

        if (in_array($name, ['start.date', 'end.date']) && $value) {
            $value = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        if (in_array($name, ['start.dateTime', 'end.dateTime']) && $value) {
            $value = Carbon::createFromFormat(DateTime::RFC3339, $value);
        }

        return $value;
    }

    // @todo
    public static function find($eventId) {

        $service = GoogleServiceCalendar::getInstance();

        $calendarId = config('geckob-google-calendar.calendar_id');

        $googleCalendar = new GoogleCalendar($service, $calendarId);

        $googleEvent = $googleCalendar->getEvent($eventId);

        return new self($googleEvent);

    }

    public function save($method = 'insertEvent') {

        $googleEvent = $this->googleCalendar->$method($this); // Will use googleEvent instead

        return new self($googleEvent);

    }

    public function delete($eventId = null) {

        $eventId = is_null($eventId)? $this->googleEvent->id : $eventId;

        $this->googleCalendar->deleteEvent($eventId);

        return $eventId;

    }

    protected function getFieldName($name) {

        $map = [
            'name'          => 'summary',
            'description'   => 'description',
            'startDate'     => 'start.date',
            'endDate'       => 'end.date',
            'startDateTime' => 'start.dateTime',
            'endDateTime'   => 'end.dateTime',
        ];

        return $map[$name];
    }

    protected function setDateProperty( $name, Carbon $date)
    {
        $eventDateTime = new Google_Service_Calendar_EventDateTime();

        if (in_array($name, ['start.date', 'end.date'])) {
            $eventDateTime->setDate($date->format('Y-m-d'));
            $eventDateTime->setTimezone($date->getTimezone());
        }

        if (in_array($name, ['start.dateTime', 'end.dateTime'])) {
            $eventDateTime->setDateTime($date->format(DateTime::RFC3339));
            $eventDateTime->setTimezone($date->getTimezone());
        }

        if (starts_with($name, 'start')) {
            $this->googleEvent->setStart($eventDateTime);
        }

        if (starts_with($name, 'end')) {
            $this->googleEvent->setEnd($eventDateTime);
        }
    }

    public static function create($arguments) {

    }
}