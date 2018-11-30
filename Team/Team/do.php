<?php
/**
 * Author: Derek
 * Date: 2018.11
 */
define('USE_BRICKER', true);
$LifeCfg = array(
    'MODULE_NAME'    => 'Team',
    'REQUEST_NAME'   => 'Team',
    'LANG'           => 'en',
    //'SESSION_CLASS'  => 'JiaSession',
    'DB_TYPE'        => 'Medoo',
    'LOAD_DB'        => array(
        'Team', 'User'
    ),
    'LOAD_LIB'       => array(
        'Common/constants.php',
        'Common/TeamupBase.php',
        'Common/utils.php'
    )
);
require '../../Bricklayer/Bricker.php';