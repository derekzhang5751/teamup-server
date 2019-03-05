<?php
/**
 * User: Derek
 * Date: 2018.11
 */

function db_insert_signup_session($signup)
{
    $data = array(
        'activate_id' => $signup['activate_id'],
        'username'    => $signup['username'],
        'password'    => $signup['password'],
        'name_type'   => $signup['name_type'],
        'reg_time'    => $signup['reg_time'],
        'status'      => 0
    );
    $stat = $GLOBALS['db']->insert('tu_signup', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        return false;
    }
}

function db_get_signup_session_by_name($username)
{
    $signup = $GLOBALS['db']->get('tu_signup',
        [
            'id', 'activate_id', 'username', 'name_type', 'reg_time', 'status'
        ],
        [
            'username' => $username
        ]
    );
    return $signup;
}

function db_get_signup_session_by_code($activateId)
{
    $signup = $GLOBALS['db']->get('tu_signup',
        [
            'id', 'activate_id', 'username', 'password', 'name_type', 'reg_time', 'status'
        ],
        [
            'activate_id' => $activateId
        ]
    );
    return $signup;
}

function db_mark_expired_session($hours)
{
    $data = array(
        'status' => -1
    );
    $stat = $GLOBALS['db']->update('tu_signup', $data,
        [
            'id' => $session['id']
        ]
    );
    return $stat->rowCount();
}

function db_activate_signup_status($activateId)
{
    $data = array(
        'status' => 1
    );
    $stat = $GLOBALS['db']->update('tu_signup', $data,
        [
            'activate_id' => $activateId
        ]
    );
    return $stat->rowCount();
}

function db_update_signup_session($signup)
{
    $data = array(
        'activate_id' => $signup['activate_id'],
        'password' => $signup['password'],
        'name_type' => $signup['name_type'],
        'reg_time' => $signup['reg_time'],
        'status' => 0
    );
    $stat = $GLOBALS['db']->update('tu_signup', $data,
        [
            'id' => $signup['id'],
            'username' => $signup['username']
        ]
    );
    return $stat->rowCount();
}
