<?php
/**
 * User: Derek
 * Date: 2018-02-28
 * Time: 6:55 PM
 */

define('MD5_KEY', '1234567890qwert');

/* Define Error Type */
define('ERROR',              -1);
define('SUCCESS',             0);
define('ERROR_NETWORK',       1);
define('ERROR_PARSE_DATA',    2);
define('ERROR_LOGIN',         3);
define('DATA_EMPTY',          4);
define('NO_PERMISION',        5);

$WEATHER_SUMMARY = [
    [ 'summary' => 'Snow',      'index' => 12 ],
    [ 'summary' => 'Rain',      'index' => 6 ],
    [ 'summary' => 'Drizzle',   'index' => 13 ],
    [ 'summary' => 'Sleet',     'index' => 13 ],
    [ 'summary' => 'Lightning', 'index' => 8 ],
    [ 'summary' => 'Foggy',     'index' => 11 ],
    [ 'summary' => 'Windy',     'index' => 10 ],
    [ 'summary' => 'Cloudy',    'index' => 1 ],
    [ 'summary' => 'Clear',     'index' => 4 ]
];
