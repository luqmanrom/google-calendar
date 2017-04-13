<?php

namespace Geckob\GoogleCalendar;

use Carbon\Carbon;
use DateTime;
use Google_Service_Calendar;

class GoogleCalendar
{
    protected $calendarService;

    protected $calendarId;

    public function __construct(Google_Service_Calendar $calendarService, $calendarId)
    {
        $this->calendarService = $calendarService;

        $this->calendarId = $calendarId;
    }

    public function getCalendarId()
    {
        return $this->calendarId;
    }


    public function listEvents(
        Carbon $startDateTime = null,
        Carbon $endDateTime = null,
        array $queryParameters = []) {
            $parameters = ['singleEvents' => true];

            if (is_null($startDateTime)) {
                $startDateTime = Carbon::now()->startOfDay();
            }

            $parameters['timeMin'] = $startDateTime->format(DateTime::RFC3339);

            if (is_null($endDateTime)) {
                $endDateTime = Carbon::now()->addYear()->endOfDay();
            }
            $parameters['timeMax'] = $endDateTime->format(DateTime::RFC3339);

            $parameters = array_merge($parameters, $queryParameters);

            return $this
                ->calendarService
                ->events
                ->listEvents($this->calendarId, $parameters)
                ->getItems();
    }


    public function getEvent($eventId)
    {
        return $this->calendarService->events->get($this->calendarId, $eventId);
    }


    public function insertEvent($event)
    {
        if ($event instanceof Event) {
            $event = $event->googleEvent; // Convert to already setup Google_Service_Calendar_Event
        }

        return $this->calendarService->events->insert($this->calendarId, $event);
    }


    public function updateEvent($event)
    {
        if ($event instanceof Event) {
            $event = $event->googleEvent; // Convert to already setup Google_Service_Calendar_Event
        }

        return $this->calendarService->events->update($this->calendarId, $event->id, $event);
    }


    public function deleteEvent($eventId)
    {
        if ($eventId instanceof Event) {
            $eventId = $eventId->id;
        }

        $this->calendarService->events->delete($this->calendarId, $eventId);
    }

    public function getService()
    {
        return $this->calendarService;
    }

}