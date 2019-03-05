<?php
/*!
 * Bricklayer PHP framework
 * Version 1.0.0
 *
 * Copyright 2019, Derek Zhang
 * Released under the MIT license
 */

namespace Bricker;

$gConfig = [
    'trace' => true,
    'dbs' => [
        'db' => [
            // required
            'database_type' => 'pgsql',
            'database_name' => 'teamup',
            'server' => 'localhost',
            'username' => 'postgres',
            'password' => 'postgres',

            // optional
            'charset' => 'utf8',
            'port' => 5432,

            // [optional] Enable logging (Logging is disabled by default for better performance)
            'logging' => false,
            'trace' => true,
            'threshold' => 0.0
        ]
    ],
    'log' => [
        'logging' => true,
        'basepath' => '/Users/derek/workspace/teamup/teamup-server/log/'
    ],
    'upload' => [
        'uploadpath' => '/Users/derek/workspace/teamup/teamup-server/upload/',
        'maxsize' => 1048576
    ]
];
