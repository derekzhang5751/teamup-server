<?php
/*!
 * Bricklayer PHP framework
 * Version 1.0.0
 *
 * Copyright 2017, Derek Zhang
 * Released under the MIT license
 */

namespace Bricker;

$gConfig = [
    'trace' => true,
    'db' => [
        // required
        'database_type' => 'pgsql',
        'database_name' => 'IOT',
        'server' => 'localhost',
        'username' => 'iot_server',
        'password' => 'iot_server',

        // optional
        'charset' => 'utf8',
        'port' => 5432,

        // [optional] Table prefix
        //'prefix' => 'iot_',

        // [optional] Enable logging (Logging is disabled by default for better performance)
        'logging' => false,
        'trace' => true,
        'threshold' => 0.1
    ],
    'log' => [
        'logging' => true,
        'basepath' => 'C:/workspace/logs/'
    ],
    'upload' => [
        'uploadpath' => 'C:/workspace/upload/',
        'maxsize' => 1048576
    ]
];
