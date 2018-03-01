<?php namespace Gzero\Core\Services;

use \DateTime;
use \DateTimeZone;

class TimezoneService {

    /**
     * Gets all timezones in the world
     *
     * @return array
     */
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
        usort($timezones, function ($t1, $t2) {
            if ($t1['offset'] < $t2['offset']) {
                return -1;
            } elseif ($t1['offset'] > $t2['offset']) {
                return 1;
            } else {
                return strcmp($t1['name'], $t2['name']);
            }
        });

        return $timezones;
    }
}
