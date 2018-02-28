<?php namespace Gzero\Core\Services;

use \DateTime;
use \DateTimeZone;

class TimezoneService {
    public function getAvailableTimezones()
    {
        $timezoneNames = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezones     = array_map(function ($timezoneName) {
            $timezone = new DateTimeZone($timezoneName);
            $offset   = $timezone->getOffset(new DateTime("now", $timezone)) / 3600;
            if ($offset > 0) {
                $offset = '+' . $offset;
            }
            return [
                'name'   => $timezoneName,
                'offset' => $offset
            ];
        }, $timezoneNames);
        usort($timezones, function ($a, $b) {
            if ($a['offset'] < $b['offset']) {
                return -1;
            } elseif ($a['offset'] > $b['offset']) {
                return 1;
            } else {
                return strcmp($a['name'], $b['name']);
            }
        });

        return $timezones;
    }
}