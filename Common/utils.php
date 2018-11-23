<?php

/**
 * User: Derek
 * Date: 2018.10
 */
function generate_random_code() {
    $code = strval(rand(1000, 9999));
    return $code;
}

function time_local_to_utc($local) {
    $dst = date('I');
    if ($dst == 1) {
        $utc = date('Y-m-d H:i:s', strtotime($local . ' + 4 hours'));
    } else {
        $utc = date('Y-m-d H:i:s', strtotime($local . ' + 5 hours'));
    }
    return $utc;
}

function time_utc_to_local($utc) {
    $dst = date('I');
    if ($dst == 1) {
        $local = date('Y-m-d H:i:s', strtotime($utc . ' - 4 hours'));
    } else {
        $local = date('Y-m-d H:i:s', strtotime($utc . ' - 5 hours'));
    }
    return $local;
}

function now_utc() {
    $now = date('Y-m-d H:i:s');
    return time_local_to_utc($now);
}

function now_local() {
    $now = date('Y-m-d H:i:s');
    return $now;
}

function create_dir($path) {
    if (!is_dir($path)) {
        if (create_dir(dirname($path))) {
            mkdir($path, 0777);
            return true;
        }
    } else {
        return true;
    }
}
