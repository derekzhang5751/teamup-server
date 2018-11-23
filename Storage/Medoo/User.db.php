<?php
/**
 * User: Derek
 * Date: 2018.10
 */

function db_check_user_login($userName, $password)
{
    $user = $GLOBALS['db']->get('iot_user',
        ['id', 'username', 'level', 'first_name', 'last_name', 'email', 'mobile', 'is_active', 'reg_time', 'desc'],
        [
            'mobile' => $userName,
            'password' => $password
        ]
    );
    return $user;
}

function db_check_admin_login($userName, $password)
{
    $user = $GLOBALS['db']->get('iot_user',
        ['id', 'username', 'level', 'first_name', 'last_name', 'email', 'mobile', 'is_active', 'reg_time', 'desc', 'dev_uid'],
        [
            'username' => $userName,
            'password' => $password,
            'level[>]' => 4
        ]
    );
    return $user;
}

function db_get_user_info($userId)
{
    $user = $GLOBALS['db']->get('iot_user',
        [
            'id', 'username', 'level', 'first_name', 'last_name', 'email', 'mobile', 'is_active', 'reg_time', 'desc',
            'dev_uid', 'token', 'dev_type', 'dev_model', 'last_time'
        ],
        [
            'id' => $userId
        ]
    );
    return $user;
}

function db_set_user_active_time($mobile, $code)
{
    $now = date('Y-m-d H:i:s');
    $data = $GLOBALS['db']->update('iot_user',
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
    $user = $GLOBALS['db']->get('iot_user',
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
    $data = $GLOBALS['db']->update('iot_user',
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

function db_check_user_building_linked($userId, $buildingId)
{
    $link = $GLOBALS['db']->get('iot_link_user_building',
        ['id', 'userid', 'building'],
        [
            'userid' => $userId,
            'building' => $buildingId
        ]
    );
    return $link;
}

function db_insert_user_building_link($userId, $buildingId)
{
    $data = array(
        'userid'   => trim($userId),
        'building' => trim($buildingId)
    );
    $stat = $GLOBALS['db']->insert('iot_link_user_building', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        return false;
    }
}

function db_delete_user_building_link($userId, $buildingId)
{
    $stat = $GLOBALS['db']->delete('iot_link_user_building',
        [
            'userid' => $userId,
            'building' => $buildingId
        ]
    );
    if ($stat->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

function db_get_user_list($buildingId)
{
    if ($buildingId == 0) {
        $buildings = $GLOBALS['db']->select('iot_user',
            [
                'id', 'username', 'level', 'first_name', 'last_name', 'email', 'mobile', 'is_active', 'reg_time', 'desc',
                'dev_uid', 'token', 'dev_type', 'dev_model'
            ],
            [
                'ORDER' => ['id' => 'DESC']
            ]
        );
    } else {
        $sql = "SELECT \"id\",username,\"level\",first_name,last_name,email,mobile,is_active,reg_time,\"desc\",dev_uid,token,dev_type,dev_model FROM iot_user";
        $sql = $sql . " WHERE \"id\" IN (SELECT userid FROM iot_link_user_building WHERE building=:building) ORDER BY \"id\" DESC";
        $buildings = $GLOBALS['db']->query($sql, [
            ":building" => $buildingId
        ])->fetchAll();
    }
    return $buildings;
}

function db_insert_user($user)
{
    $data = array(
        'username'   => trim($user['username']),
        'password'   => '1234',
        'level'      => trim($user['level']),
        'first_name' => trim($user['first_name']),
        'last_name'  => trim($user['last_name']),
        'email'      => trim($user['email']),
        'mobile'     => trim($user['mobile']),
        'is_active'  => trim($user['is_active']),
        'reg_time'   => trim($user['reg_time']),
        'desc'       => trim($user['desc'])
    );
    $stat = $GLOBALS['db']->insert('iot_user', $data);
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
        'level'      => trim($user['level']),
        'first_name' => trim($user['first_name']),
        'last_name'  => trim($user['last_name']),
        'email'      => trim($user['email']),
        'mobile'     => trim($user['mobile']),
        'is_active'  => trim($user['is_active']),
        'desc'       => trim($user['desc'])
    );
    $data = $GLOBALS['db']->update('iot_user', $cols,
        [
            'id' => $user['id']
        ]
    );
    return $data->rowCount();
}

function db_update_user_dev_uid($username, $deviceUid)
{
    $now = date('Y-m-d H:i:s');
    $data = $GLOBALS['db']->update('iot_user',
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
    $user = $GLOBALS['db']->get('iot_user',
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

function db_select_user_link_building($userId)
{
    $link = $GLOBALS['db']->select('iot_link_user_building',
        ['id', 'userid', 'building'],
        [
            'userid' => $userId
        ]
    );
    return $link;
}
