<?php
/**
 * User: Derek
 * Date: 2019.08
 */

function db_insert_notification($data) {
    $stat = $GLOBALS['db']->insert('mnt_notification', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        //exit (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}
