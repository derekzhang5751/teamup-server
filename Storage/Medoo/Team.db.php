<?php
/**
 * User: Derek
 * Date: 2018.10
 */

function db_get_buildings()
{
    $buildings = $GLOBALS['db']->select('iot_building',
        ['id', 'name', 'address', 'lat', 'lon', 'model', 'source_table'],
        [
            'ORDER' => ['id' => 'DESC']
        ]
    );
    return $buildings;
}

function db_get_api_buildings()
{
    $buildings = $GLOBALS['db']->select('api_building',
        ['id', 'name', 'address', 'lat', 'lon', 'owner_id', 'model', 'height', 'table_name'],
        [
            'ORDER' => 'id'
        ]
    );
    return $buildings;
}

function db_insert_building($building)
{
    $data = array(
        'name'         => trim($building['name']),
        'address'      => trim($building['address']),
        'lat'          => trim($building['lat']),
        'lon'          => trim($building['lon']),
        'model'        => trim($building['model']),
        'source_table' => trim($building['source_table'])
    );
    $stat = $GLOBALS['db']->insert('iot_building', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        //exit (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_get_buildings_of_user($userId)
{
    $sql = "SELECT id,name,address,lat,lon,model,source_table FROM iot_building WHERE id IN (SELECT building FROM iot_link_user_building WHERE userid=:userid) ORDER BY id DESC";
    $buildings = $GLOBALS['db']->query($sql, [
        ":userid" => $userId
    ])->fetchAll();
    //var_dump( $GLOBALS['db']->error() );
    return $buildings;
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

function db_get_equipments_of_building($buildingId)
{
    $sql = "SELECT id,name,unit,type,building_id,floor,device_id,pic_url,\"desc\" FROM iot_sensor WHERE building_id=:building_id AND (type='Equipment' OR type='Vibration' OR type='Power' OR type='Occupancy' OR type='easylink') ORDER BY id ASC";
    $equipments = $GLOBALS['db']->query($sql, [
        ":building_id" => $buildingId
    ])->fetchAll();
    //var_dump( $GLOBALS['db']->error() );
    return $equipments;
}

function db_get_temp_sensors_of_floor($buildingId, $floor)
{
    $sql = "SELECT id,name,unit,type,building_id,floor,device_id,pic_url,\"desc\" FROM iot_sensor WHERE building_id=:building_id AND floor=:floor AND type='Temperature' ORDER BY id ASC";
    $sensors = $GLOBALS['db']->query($sql, [
        ":building_id" => $buildingId,
        ":floor" => $floor
    ])->fetchAll();
    //var_dump( $GLOBALS['db']->error() );
    return $sensors;
}

function db_get_humi_sensors_of_floor($buildingId, $floor)
{
    $sql = "SELECT id,name,unit,type,building_id,floor,device_id,pic_url,\"desc\" FROM iot_sensor WHERE building_id=:building_id AND floor=:floor AND type='Humidity' ORDER BY id ASC";
    $sensors = $GLOBALS['db']->query($sql, [
        ":building_id" => $buildingId,
        ":floor" => $floor
    ])->fetchAll();
    //var_dump( $GLOBALS['db']->error() );
    return $sensors;
}

function db_update_building($building)
{
    if ($building['id'] <= 0) {
        return false;
    }
    $cols = array(
        'name'         => trim($building['name']),
        'address'      => trim($building['address']),
        'lat'          => trim($building['lat']),
        'lon'          => trim($building['lon']),
        'model'        => trim($building['model']),
        'source_table' => trim($building['source_table'])
    );
    $data = $GLOBALS['db']->update('iot_building', $cols,
        [
            'id' => $building['id']
        ]
    );
    return $data->rowCount();
}
