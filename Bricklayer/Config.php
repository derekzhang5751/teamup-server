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
        'database_type' => 'mysql',
        'database_name' => 'teamup',
        'server' => 'localhost',
        'username' => 'teamup',
        'password' => 'Abcd1234!',

        // optional
        'socket' => '/var/lib/mysql/mysql.sock',
        'charset' => 'utf8',
        'port' => 3306,

        // [optional] Table prefix
        //'prefix' => 'iot_',

        // [optional] Enable logging (Logging is disabled by default for better performance)
        'logging' => false,
        'trace' => true,
        'threshold' => 0.1
    ],
    'log' => [
        'logging' => true,
        'basepath' => '/usr/local/apache2/htdocs/log/'
    ],
    'upload' => [
        'uploadpath' => '/usr/local/apache2/htdocs/upload/',
        'maxsize' => (1048576 * 5)
    ]
];
