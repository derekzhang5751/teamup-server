<?php
/**
 * User: Derek
 * Date: 2018.11
 */


function db_select_teams($conditions)
{
    $teams = $GLOBALS['db']->select('tu_team',
        [
            'id', 'author', 'category', 'time_begin', 'time_end', 'need_review', 'dp_self',
            'dp_other', 'create_time', 'status', 'people', 'title', 'location', 'desc'
        ],
        $conditions
    );
    return $teams;
}

function db_get_team_info($teamId)
{
    $team = $GLOBALS['db']->get('tu_team',
        [
            'id', 'author', 'category', 'time_begin', 'time_end', 'need_review', 'dp_self',
            'dp_other', 'create_time', 'status', 'people', 'title', 'location', 'desc'
        ],
        [
            'id' => $teamId
        ]
    );
    return $team;
}

function db_insert_team($team)
{
    $data = array(
        'author'      => $team['author'],
        'category'    => $team['category'],
        'time_begin'  => time_local_to_utc( trim($team['time_begin']) ),
        'time_end'    => trim($team['time_end']),
        'need_review' => $team['need_review'],
        'dp_self'     => $team['dp_self'],
        'dp_other'    => $team['dp_other'],
        'create_time' => now_utc(),
        'status'      => $team['status'],
        'people'      => $team['people'],
        'title'       => trim($team['title']),
        'location'    => trim($team['location']),
        'desc'        => trim($team['desc'])
    );
    $stat = $GLOBALS['db']->insert('tu_team', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        //exit (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_update_team($team)
{
    $cols = array(
        'author'      => $team['author'],
        'category'    => $team['category'],
        'time_begin'  => time_local_to_utc( trim($team['time_begin']) ),
        'time_end'    => trim($team['time_end']),
        'need_review' => $team['need_review'],
        'dp_self'     => $team['dp_self'],
        'dp_other'    => $team['dp_other'],
        //'create_time' => now_utc(),
        'status'      => $team['status'],
        'people'      => $team['people'],
        'title'       => trim($team['title']),
        'location'    => trim($team['location']),
        'desc'        => trim($team['desc'])
    );
    $data = $GLOBALS['db']->update('tu_team', $cols,
        [
            'id' => $team['id']
        ]
    );
    return $data->rowCount();
}

function db_update_team_status($id, $status)
{
    $cols = array(
        'status' => $status
    );
    $data = $GLOBALS['db']->update('tu_team', $cols,
        [
            'id' => $id
        ]
    );
    return $data->rowCount();
}

function db_exist_link_user_team($userId, $teamId)
{
    $exist = $GLOBALS['db']->has('tu_link_user_team',
        [
            'user_id' => $userId,
            'team_id' => $teamId
        ]
    );
    return $exist;
}

function db_insert_link_user_team($userId, $teamId, $status, $remark)
{
    $data = array(
        'user_id'   => $userId,
        'team_id'   => $teamId,
        'status'    => $status,
        'remark'    => $remark
    );
    $stat = $GLOBALS['db']->insert('tu_link_user_team', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        //exit (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_update_link_user_team($userId, $teamId, $status, $remark)
{
    $data = array(
        'status'    => $status,
        'remark'    => $remark
    );
    $condition = array(
        'user_id'   => $userId,
        'team_id'   => $teamId
    );
    $stat = $GLOBALS['db']->update('tu_link_user_team', $data, $condition);
    if ($stat->rowCount() > 0) {
        return true;
    } else {
        //exit (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_select_apply_user_of_team($teamId) {
    $users = $GLOBALS['db']->select('tu_link_user_team',
        [
            "[>]tu_user" => ["user_id" => "id"]
        ],
        [
            'tu_link_user_team.id', 'tu_link_user_team.user_id', 'tu_link_user_team.team_id',
            'tu_link_user_team.status', 'tu_link_user_team.remark', 'tu_user.first_name'
        ],
        [
            'tu_link_user_team.team_id' => $teamId,
            'tu_link_user_team.status[>]' => 0,
            'ORDER' => [
                "tu_link_user_team.status" => "DESC"
            ]
        ]
    );
    return $users;
}

function db_get_floors_of_building($buildingId, $desc)
{
    //$sql = "SELECT distinct(floor) FROM iot_sensor WHERE building_id=:building_id AND floor>0 ORDER BY floor";
    $sql = "SELECT DISTINCT(floor) FROM iot_sensor WHERE building_id=:building_id AND (type='Temperature' OR type='Humidity') ORDER BY floor";
    $sql = $sql.' '.$desc;
    $floors = $GLOBALS['db']->query($sql, [
        ":building_id" => $buildingId
    ])->fetchAll();
    //var_dump( $GLOBALS['db']->error() );
    return $floors;
}
