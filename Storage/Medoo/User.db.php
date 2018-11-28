<?php
/**
 * User: Derek
 * Date: 2018.11
 */

function db_check_user_login($userName, $password)
{
    $user = $GLOBALS['db']->get('tu_user',
        [
            'id', 'username', 'level', 'first_name', 'last_name', 'email', 'mobile', 'sex',
            'birthday', 'is_active', 'reg_time', 'desc', 'photo_url'
        ],
        [
            'email' => $userName,
            'password' => $password
        ]
    );
    return $user;
}

function db_get_user_info($userId)
{
    $user = $GLOBALS['db']->get('tu_user',
        [
            'id', 'username', 'level', 'first_name', 'last_name', 'email', 'mobile', 'sex',
            'birthday', 'is_active', 'reg_time', 'desc', 'photo_url'
        ],
        [
            'id' => $userId
        ]
    );
    return $user;
}

function db_insert_user_session($session)
{
    $data = array(
        'user_id'    => $session['user_id'],
        'last_time'  => $session['last_time'],
        'session_id' => $session['session_id'],
        'dev_uid'    => $session['dev_uid'],
        'dev_type'   => $session['dev_type'],
        'dev_model'  => $session['dev_model'],
        'token'      => $session['token']
    );
    $stat = $GLOBALS['db']->insert('tu_session', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        return false;
    }
}

function db_get_user_session($sessionId)
{
    $session = $GLOBALS['db']->get('tu_session',
        [
            'id', 'user_id', 'last_time', 'session_id', 'dev_uid', 'dev_type', 'dev_model', 'token'
        ],
        [
            'session_id' => $sessionId
        ]
    );
    return $session;
}

function db_update_user_session($session)
{
    $data = array();
    if (isset($session['session_id'])) {
        $data['session_id'] = $session['session_id'];
    }
    $stat = $GLOBALS['db']->update('tu_session', $data,
        [
            'id' => $session['id']
        ]
    );
    return $stat->rowCount();
}

//////////////////////////////////////////////////////////
function db_check_admin_login($userName, $password)
{
    $user = $GLOBALS['db']->get('tu_user',
        ['id', 'username', 'level', 'first_name', 'last_name', 'email', 'mobile', 'is_active', 'reg_time', 'desc', 'dev_uid'],
        [
            'username' => $userName,
            'password' => $password,
            'level[>]' => 4
        ]
    );
    return $user;
}

function db_set_user_active_time($mobile, $code)
{
    $now = date('Y-m-d H:i:s');
    $data = $GLOBALS['db']->update('tu_user',
        [
            'last_time' => $now,
            'password' => $code
        ],
        [
            'mobile' => $mobile
        ]
    );
    return $data->rowCount();
}

function db_get_user_active_time($mobile)
{
    $user = $GLOBALS['db']->get('tu_user',
        ['id', 'username', 'last_time'],
        [
            'mobile' => $mobile
        ]
    );
    return $user;
}

function db_update_user_device_info($mobile, $data)
{
    $now = date('Y-m-d H:i:s');
    $data = $GLOBALS['db']->update('tu_user',
        [
            'dev_uid' => $data['dev_uid'],
            'token' => $data['token'],
            'dev_type' => $data['dev_type'],
            'dev_model' => $data['dev_model']
        ],
        [
            'mobile' => $mobile
        ]
    );
    return $data->rowCount();
}

function db_check_user_team_linked($userId, $teamId)
{
    $link = $GLOBALS['db']->get('tu_link_user_team',
        ['id', 'user_id', 'team_id', 'status'],
        [
            'user_id' => $userId,
            'team_id' => $teamId
        ]
    );
    return $link;
}

function db_insert_user_team_link($userId, $teamId, $status)
{
    $data = array(
        'user_id' => $userId,
        'team_id' => $teamId,
        'status'  => $status
    );
    $stat = $GLOBALS['db']->insert('tu_link_user_team', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        return false;
    }
}

function db_delete_user_team_link($userId, $teamId)
{
    $stat = $GLOBALS['db']->delete('tu_link_user_team',
        [
            'user_id' => $userId,
            'team_id' => $teamId
        ]
    );
    if ($stat->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

function db_insert_user($user)
{
    $data = array(
        'username'   => trim($user['username']),
        'password'   => '1234',
        'level'      => $user['level'],
        'first_name' => trim($user['first_name']),
        'last_name'  => trim($user['last_name']),
        'email'      => trim($user['email']),
        'mobile'     => trim($user['mobile']),
        'is_active'  => $user['is_active'],
        'reg_time'   => trim($user['reg_time']),
        'desc'       => trim($user['desc'])
    );
    $stat = $GLOBALS['db']->insert('tu_user', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        return false;
    }
}

function db_update_user($user)
{
    if ($user['id'] <= 0) {
        return false;
    }
    $cols = array(
        'username'   => trim($user['username']),
        'level'      => $user['level'],
        'first_name' => trim($user['first_name']),
        'last_name'  => trim($user['last_name']),
        'email'      => trim($user['email']),
        'mobile'     => trim($user['mobile']),
        'is_active'  => $user['is_active'],
        'desc'       => trim($user['desc'])
    );
    $data = $GLOBALS['db']->update('tu_user', $cols,
        [
            'id' => $user['id']
        ]
    );
    return $data->rowCount();
}

function db_update_user_dev_uid($username, $deviceUid)
{
    $now = date('Y-m-d H:i:s');
    $data = $GLOBALS['db']->update('tu_user',
        [
            'dev_uid' => $deviceUid,
            'last_time' => $now
        ],
        [
            'username' => $username
        ]
    );
    return $data->rowCount();
}

function db_get_user_by_uuid($uuid)
{
    $user = $GLOBALS['db']->get('tu_user',
        [
            'id', 'username', 'level', 'first_name', 'last_name', 'email', 'mobile', 'is_active', 'reg_time', 'desc',
            'dev_uid', 'token', 'dev_type', 'dev_model', 'last_time'
        ],
        [
            'dev_uid' => $uuid
        ]
    );
    return $user;
}

function db_select_user_link_team($userId)
{
    $links = $GLOBALS['db']->select('tu_link_user_team',
        ['id', 'user_id', 'team_id', 'status'],
        [
            'user_id' => $userId
        ]
    );
    return $links;
}
