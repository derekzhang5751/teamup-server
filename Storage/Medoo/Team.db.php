<?php
/**
 * User: Derek
 * Date: 2018.10
 */


function db_select_teams($conditions)
{
    $buildings = $GLOBALS['db']->select('tu_team',
        ['id', 'author', 'category', 'time_begin', 'time_end', 'need_review', 'dp_self', 'dp_other', 'create_time', 'status', 'people', 'title', 'location', 'desc'],
        $conditions
    );
    return $buildings;
}

function db_insert_team($team)
{
    $now = date('Y-m-d H:i:s');
    $data = array(
        'author'      => $team['author'],
        'category'    => $team['category'],
        'time_begin'  => trim($team['time_begin']),
        'time_end'    => trim($team['time_end']),
        'need_review' => $team['review'],
        'dp_self'     => $team['drop_self'],
        'dp_other'    => $team['drop_other'],
        'create_time' => $now,
        'status'      => $team['status'],
        'people'      => $team['people_min'],
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
