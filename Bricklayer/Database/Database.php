<?php
/*!
 * Bricklayer PHP framework
 * Version 1.0.0
 *
 * Copyright 2017, Derek Zhang
 * Released under the MIT license
 */

namespace Bricker;

require BRICKER_PATH . '/Database/Medoo/Medoo.php';

use Medoo\Medoo;

function initDatabases() {
    global $gConfig;
    
    foreach ($gConfig['dbs'] as $key => $value) {
        $GLOBALS[$key] = new Medoo($value);
    }
}
