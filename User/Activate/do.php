<?php
/**
 * Author: Derek
 * Date: 2019.02
 */
define('USE_BRICKER', true);
$LifeCfg = array(
    'MODULE_NAME'    => 'User',
    'REQUEST_NAME'   => 'Activate',
    'LANG'           => 'en',
    //'SESSION_CLASS'  => 'JiaSession',
    'DB_TYPE'        => 'Medoo',
    'LOAD_DB'        => array(
        'User', 'Signup'
    ),
    'LOAD_LIB'       => array(
        'Common/constants.php',
        'Common/TeamupBase.php',
        'Common/utils.php'
    )
);
require '../../Bricklayer/Bricker.php';