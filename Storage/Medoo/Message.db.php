<?php
/**
 * User: Derek
 * Date: 2018.10
 */

function db_select_triggers_of_user($userId, $buildingId)
{
    $triggers = $GLOBALS['db']->select('iot_message_trigger',
        ['id', 'user_id', 'building_id', 'msg_type', 'status', 'frequency', 'trigger_name', 'msg_title', 'msg_templet', 'week_days', 'time_begin', 'time_end'],
        [
            'user_id' => $userId,
            'building_id' => $buildingId,
            'ORDER' => 'id'
        ]
    );
    return $triggers;
}

function db_select_conditions_of_trigger($triggerId)
{
    $conditions = $GLOBALS['db']->select('iot_trigger_condition',
        ['id', 'trigger_id', 'week_days', 'position', 'factor', 'compare', 'threshold'],
        [
            'trigger_id' => $triggerId,
            'ORDER' => 'id'
        ]
    );
    return $conditions;
}

function db_select_message_of_user($userId, $buildingId, $status, $type)
{
    if ($type == 0) {
        $condition = [
            'user_id' => $userId,
            'building_id' => $buildingId,
            'status' => $status,
            'type[<]' => 100,
            'ORDER' => ['id' => 'DESC']
        ];
    } else {
        $condition = [
            'user_id' => $userId,
            'building_id' => $buildingId,
            'status' => $status,
            'type[>=]' => 100,
            'ORDER' => ['id' => 'DESC']
        ];
    }
    $triggers = $GLOBALS['db']->select('iot_message',
        ['id', 'trigger_id', 'user_id', 'building_id', 'status', 'content', 'title', 'device_id', 'trigger_time', 'type'],
        $condition
    );
    //echo $GLOBALS['db']->last();
    return $triggers;
}

function db_get_message_by_id($messageId)
{
    $condition = [
        'id' => $messageId
    ];
    $message = $GLOBALS['db']->get('iot_message',
        ['id', 'trigger_id', 'user_id', 'building_id', 'status', 'content', 'title', 'device_id', 'trigger_time', 'type'],
        $condition
    );
    //echo $GLOBALS['db']->last();
    return $message;
}

function db_insert_trigger_of_user($userId, $buildingId, $trigger) {
    $timeBegin = '09:00';
    if (isset($trigger['begin']) && $trigger['begin']) {
        $timeBegin = $trigger['begin'];
    }
    $timeEnd = '18:00';
    if (isset($trigger['end']) && $trigger['end']) {
        $timeEnd = $trigger['end'];
    }
    
    $data = array(
        'user_id'     => $userId,
        'building_id' => $buildingId,
        'msg_type'    => $trigger['msg_type'],
        'status'      => $trigger['status'],
        'frequency'   => $trigger['frequency'],
        'trigger_name'=> 'Triger Name',
        'msg_title'   => 'Message Title',
        'msg_templet' => $trigger['msg_templet'],
        'week_days'   => $trigger['week_days'],
        'time_begin'  => $timeBegin,
        'time_end'    => $timeEnd,
    );
    $stat = $GLOBALS['db']->insert('iot_message_trigger', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        echo (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_insert_conditions_of_trigger($triggerId, $conditions) {
    $datas = array();
    foreach ($conditions as $condition) {
        $data = array(
            'trigger_id'=> $triggerId,
            'week_days' => '',
            'position'  => $condition['position'],
            'factor'    => $condition['factor'],
            'compare'   => $condition['compare'],
            'threshold' => $condition['threshold'],
        );
        array_push($datas, $data);
    }
    $stat = $GLOBALS['db']->insert('iot_trigger_condition', $datas);
    if ($stat->rowCount() >= 1) {
        return $GLOBALS['db']->id();
    } else {
        echo (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_action_save_trigger($userId, $buildingId, $trigger)
{
    $ret = true;
    $pdo = $GLOBALS['db']->pdo;
    $pdo->beginTransaction();
    try {
        // insert trigger
        $sql = sprintf("INSERT INTO iot_message_trigger ('user_id', 'building_id', 'msg_type', 'status', 'frequency', 'trigger_name', 'msg_title', 'msg_templet', 'week_days')VALUES(%s,%s,%s,%s,%s,'%s','%s','%s','%s')",
                $userId, $buildingId, $trigger['msg_type'], $trigger['status'], $trigger['frequency'], '', '', $trigger['msg_templet'], $trigger['week_days']);
        $pdo->exec($sql);
        echo $sql;
        $recId = $pdo->lastInsertId('id');
        echo '====' . $recId;
        // insert conditions
        $conditions = $trigger['conditions'];
        foreach ($conditions as $cond) {
            $sql = sprintf("INSERT INTO iot_message_trigger ('trigger_id', 'week_days', 'position', 'factor', 'compare', 'threshold')VALUES(%s,'%s','%s','%s',%s,%s)",
                    $recId, '', $cond['position'], $cond['factor'], $cond['compare'], $cond['threshold']);
            $pdo->exec($sql);
            echo '====' . $sql;
        }
        // commit
        $pdo->commit();
    } catch (Exception $ex) {
        $pdo->rollBack();
        $ret = false;
        echo '==============ROLLBACK==============';
    }
    
    return $ret;
}

function db_delete_trigger($userId, $buildingId, $triggerId)
{
    $data = array(
        'id'     => $triggerId,
        'user_id'     => $userId,
        'building_id' => $buildingId,
    );
    $stat = $GLOBALS['db']->delete('iot_message_trigger', $data);
    return $stat->rowCount();
}

function db_delete_rules_of_trigger($triggerId)
{
    $data = array(
        'trigger_id' => $triggerId,
    );
    $stat = $GLOBALS['db']->delete('iot_trigger_condition', $data);
    return $stat->rowCount();
}

function db_get_triggers_by_id($triggerId)
{
    $trigger = $GLOBALS['db']->get('iot_message_trigger',
        ['id', 'user_id', 'building_id', 'msg_type', 'status', 'frequency', 'trigger_name', 'msg_title', 'msg_templet', 'week_days', 'time_begin', 'time_end'],
        [
            'id' => $triggerId
        ]
    );
    return $trigger;
}

function db_insert_push_message($message) {
    $stat = $GLOBALS['db']->insert('iot_message', $message);
    if ($stat->rowCount() >= 1) {
        return $GLOBALS['db']->id();
    } else {
        echo (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_count_user_msg($condition)
{
    $count = $GLOBALS['db']->count('iot_message', $condition);
    return $count;
}

function db_set_status_of_message($messageId, $status)
{
    $data = $GLOBALS['db']->update('iot_message',
        [
            'status' => $status
        ],
        [
            'id' => $messageId
        ]
    );
    return $data->rowCount();
}

function db_set_status_of_trigger($triggerId, $status, $lastTime)
{
    $data = $GLOBALS['db']->update('iot_message_trigger',
        [
            'status' => $status,
            'last_time' => $lastTime
        ],
        [
            'id' => $triggerId
        ]
    );
    return $data->rowCount();
}
