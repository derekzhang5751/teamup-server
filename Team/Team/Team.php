<?php
/**
 * Author: Derek
 * Date: 2018.11
 */
class Team extends TeamupBase {
    private $action;
    private $buildingId;
    private $today;
    private $floor;
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    public function __construct() {
    }

    protected function prepareRequestParams() {
        $this->action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
        $this->buildingId = isset($_REQUEST['buildingid']) ? trim($_REQUEST['buildingid']) : '0';
        $this->today = isset($_REQUEST['today']) ? trim($_REQUEST['today']) : '';
        if (empty($this->today)) {
            $this->today = date('Y-m-d');
        }
        $this->floor = isset($_REQUEST['floor']) ? trim($_REQUEST['floor']) : '0';
        $this->return['data']['today'] = $this->today;
        $this->return['data']['buildingid'] = $this->buildingId;
        $this->return['data']['floor'] = $this->floor;
        return true;
    }

    protected function process() {
        $this->return['data']['action'] = $this->action;
        if ($this->action == 'out_temp') {
            $this->processOutTemperature();
        } else if ($this->action == 'out_temp_day') {
            $this->processOutTempOfDay();
        } else if ($this->action == 'floors_comfort') {
            $this->processFloorsComfort();
        } else if ($this->action == 'equip_day') {
            $this->processEquipmentsOfDay();
        } else if ($this->action == 'floor_temp_day') {
            $this->processFloorTempOfDay();
        } else if ($this->action == 'floor_humi_day') {
            $this->processFloorHumiOfDay();
        }
        return true;
    }

    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }

    private function processOutTemperature() {
        $this->return['success'] = true;
        $this->return['code'] = SUCCESS;
        $day = date('Y-m-d', strtotime($this->today . ' - 1 days'));
        $this->return['data']['temperature1'] = [
            'date' => $day,
            'max' => $this->getForecastTempMaxInOneDay($this->buildingId, $day),
            'min' => $this->getForecastTempMinInOneDay($this->buildingId, $day),
            'summary' => $this->getForecastWeather($this->buildingId, $day),
            'cur' => 0
        ];
        $day = $this->today;
        $this->return['data']['temperature2'] = [
            'date' => $day,
            'max' => $this->getForecastTempMaxInOneDay($this->buildingId, $day),
            'min' => $this->getForecastTempMinInOneDay($this->buildingId, $day),
            'summary' => $this->getForecastWeather($this->buildingId, $day),
            'cur' => $this->getCurrentTemperature($this->buildingId)
        ];
        $day = date('Y-m-d', strtotime($this->today . ' + 1 days'));
        $this->return['data']['temperature3'] = [
            'date' => $day,
            'max' => $this->getForecastTempMaxInOneDay($this->buildingId, $day),
            'min' => $this->getForecastTempMinInOneDay($this->buildingId, $day),
            'summary' => $this->getForecastWeather($this->buildingId, $day),
            'cur' => 0
        ];
        $sensors = $this->getOutsideTempSensorOfBuilding($this->buildingId);
        if ($sensors) {
            $sensor = $sensors[0];
            $this->return['data']['temperature2']['cur'] = 20;
        }
    }
    private function processOutTempOfDay() {
        $this->return['success'] = true;
        $this->return['code'] = SUCCESS;
        $day = $this->today;
        $this->return['data']['forecast'] = $this->getForecastTempOfDay($this->buildingId, $day);
        $this->return['data']['realtemp'] = [];
        $sensors = $this->getOutsideTempSensorOfBuilding($this->buildingId);
        if ($sensors) {
            $sensor = $sensors[0];
            $this->return['data']['realtemp'] = $this->getFormattedSensorDataOfDay($sensor['id'], $day);
        }
    }
    private function processFloorsComfort() {
        $floors = db_get_floors_of_building($this->buildingId, 'DESC');
        if ($floors) {
            $category = [];
            $floorsTemp = [];
            $floorsHumi = [];
            foreach ($floors as $item) {
                $floor = $item['floor'];
                if ($floor == -1) {
                    $floorName = 'Basement'; // 'Basement' 'Floor -1'
                } else {
                    $floorName = 'Floor ' . $floor;
                }
                array_push($category, $floorName);
                // Temperature in the floor
                $sensors = $this->getFloorTempSensorOfBuilding($this->buildingId, $floor);
                if ($sensors) {
                    $average1 = 999;
                    $average2 = 999;
                    foreach ($sensors as $sensor) {
                        $past = $this->getSensor15Min($sensor['id']);
                        if ($average1 == 999) {
                            $average1 = $past[0];
                        } else {
                            $average1 = ($average1 + $past[0]) / 2;
                        }
                        if ($average2 == 999) {
                            $average2 = $past[1];
                        } else {
                            $average2 = ($average2 + $past[1]) / 2;
                        }
                    }
                    $average1 = round($average1, 1);
                    $average2 = round($average2, 1);
                    $floorTemp = [$floorName, $average1, $average2];
                    array_push($floorsTemp, $floorTemp);
                }
                // Humidity in the floor
                $sensors = $this->getFloorHumiSensorOfBuilding($this->buildingId, $floor);
                if ($sensors) {
                    $average1 = 999;
                    $average2 = 999;
                    foreach ($sensors as $sensor) {
                        $past = $this->getSensor15Min($sensor['id']);
                        if ($average1 == 999) {
                            $average1 = $past[0];
                        } else {
                            $average1 = ($average1 + $past[0]) / 2;
                        }
                        if ($average2 == 999) {
                            $average2 = $past[1];
                        } else {
                            $average2 = ($average2 + $past[1]) / 2;
                        }
                    }
                    $average1 = round($average1, 1);
                    $average2 = round($average2, 1);
                    $floorHumi = [$floorName, $average1, $average2];
                    array_push($floorsHumi, $floorHumi);
                }
            }
            $this->return['data']['floors'] = $category;
            $this->return['data']['floorsTemp'] = $floorsTemp;
            //$this->return['data']['labelTemp'] = [];
            $this->return['data']['floorsHumi'] = $floorsHumi;
            //$this->return['data']['labelHumi'] = [];
        } else {
            $this->return['success'] = false;
            $this->return['code'] = DATA_EMPTY;
        }
    }
    private function processEquipmentsOfDay() {
        $equipments = db_get_equipments_of_building($this->buildingId);
        if ($equipments) {
            // Sort equipments array
            $equips_sort = [];
            foreach ($equipments as $equip) {
                if ($equip['type'] == 'Power') {
                    array_push($equips_sort, $equip);
                    break;
                }
            }
            foreach ($equipments as $equip) {
                if ($equip['type'] == 'Occupancy') {
                    array_push($equips_sort, $equip);
                    break;
                }
            }
            foreach ($equipments as $equip) {
                if ($equip['type'] != 'Power' && $equip['type'] != 'Occupancy') {
                    array_push($equips_sort, $equip);
                }
            }
            // Sort Completed
            $this->return['data']['equipments'] = $equips_sort;
            foreach ($equips_sort as $equip) {
                $equipName = $equip['name'];
                $sensorData = [];
                if ($equip['type'] == 'Power') {
                    $sensorData = $this->getElegantSensorDataOfDay($equip['id'], $this->today);
                } else if ($equip['type'] == 'Occupancy') {
                    $sensorData = $this->getElegantSensorDataOfDay($equip['id'], $this->today);
                }
                if (empty($sensorData)) {
                    $sensorData = $this->getFormattedSensorDataOfDay($equip['id'], $this->today);
                }
                if ($sensorData) {
                    $equipData = [];
                    foreach ($sensorData as $sensor) {
                        if ($equip['type'] == 'Vibration' || $equip['type'] == 'Equipment' || $equip['type'] == 'easylink') {
                            if ($sensor['value'][1] == '1') {
                                $v = '200';
                            } else {
                                $v = '0';
                            }
                        } else if ($equip['type'] == 'Occupancy') {
                            if ($sensor['value'][1] < 0) {
                                $v = 0;
                            } else {
                                $v = $sensor['value'][1];
                            }
                        } else {
                            $v = $sensor['value'][1];
                        }
                        $d = [
                            'name' => $sensor['name'],
                            'value' => [$sensor['value'][0], $v]
                        ];
                        array_push($equipData, $d);
                    }
                    $this->return['data'][$equipName] = $equipData;
                } else {
                    $this->return['data'][$equipName] = [];
                }
            }
        } else {
            $this->return['success'] = false;
            $this->return['code'] = DATA_EMPTY;
        }
    }
    private function processFloorTempOfDay() {
        $sensors = db_get_temp_sensors_of_floor($this->buildingId, $this->floor);
        if ($sensors) {
            $this->return['data']['sensors'] = $sensors;
            foreach ($sensors as $equip) {
                $equipId = $equip['id'];
                $sensorData = $this->getFormattedSensorDataOfDay($equipId, $this->today);
                if ($sensorData) {
                    $this->return['data'][(string) $equipId] = $sensorData;
                } else {
                    $this->return['data'][(string) $equipId] = [];
                }
            }
        } else {
            $this->return['success'] = false;
            $this->return['code'] = DATA_EMPTY;
        }
    }
    private function processFloorHumiOfDay() {
        $sensors = db_get_humi_sensors_of_floor($this->buildingId, $this->floor);
        if ($sensors) {
            $this->return['data']['sensors'] = $sensors;
            foreach ($sensors as $equip) {
                $equipId = $equip['id'];
                $sensorData = $this->getFormattedSensorDataOfDay($equipId, $this->today);
                if ($sensorData) {
                    $this->return['data'][(string) $equipId] = $sensorData;
                } else {
                    $this->return['data'][(string) $equipId] = [];
                }
            }
        } else {
            $this->return['success'] = false;
            $this->return['code'] = DATA_EMPTY;
        }
    }
    
    /*
     * Below functions may be moved to base class IotBase
     * if they are called by other modules
     */
    public function getOutsideTempSensorOfBuilding($buildingId) {
        $conditions = [
            'building_id' => $buildingId,
            'floor' => 0,
            'type' => 'Temperature',
        ];
        $sensor = db_select_sensors($conditions);
        return $sensor;
    }
    public function getFloorTempSensorOfBuilding($buildingId, $floor) {
        $conditions = [
            'building_id' => $buildingId,
            'floor' => $floor,
            'type' => 'Temperature',
        ];
        $sensor = db_select_sensors($conditions);
        return $sensor;
    }
    public function getFloorHumiSensorOfBuilding($buildingId, $floor) {
        $conditions = [
            'building_id' => $buildingId,
            'floor' => $floor,
            'type' => 'Humidity',
        ];
        $sensor = db_select_sensors($conditions);
        return $sensor;
    }
    public function getSensorMaxInOneDay($sid, $day) {
        $day1 = $day;
        $day2 = date('Y-m-d', strtotime($day . ' + 1 days'));
        $conditions = [
            'sid' => $sid,
            'oc_time[>=]' => $day1,
            'oc_time[<]' => $day2,
        ];
        $max = db_get_max_value($conditions);
        if ($max == null) {
            $max = 0.0;
        }
        return $max;
    }
    public function getSensorMinInOneDay($sid, $day) {
        $day1 = $day;
        $day2 = date('Y-m-d', strtotime($day . ' + 1 days'));
        $conditions = [
            'sid' => $sid,
            'oc_time[>=]' => $day1,
            'oc_time[<]' => $day2,
        ];
        $min = db_get_min_value($conditions);
        if ($min == null) {
            $min = 0.0;
        }
        return $min;
    }
    public function getElegantSensorDataOfDay($sid, $day) {
        $data = [];
        $day1 = time_local_to_utc($day);
        $day2 = date('Y-m-d H:i:s', strtotime($day1 . ' + 1 days'));
        //$this->log->log('building', 'Get Elegant Data [' . $sid . '] ' . $day1 . ' - ' . $day2);
        $conditions = [
            'sid' => $sid,
            'oc_time[>=]' => $day1,
            'oc_time[<]' => $day2,
            'level15' => true,
            'ORDER' => ['id' => 'ASC']
        ];
        $elegant = db_select_elegant_data($conditions);
        $i = 0;
        foreach ($elegant as $record) {
            $name = sprintf("%02d", $i);
            $st = time_utc_to_local($record['oc_time']);
            $item = [
                'name' => $name,
                'value' => [$st, $record['value']]
            ];
            array_push($data, $item);
            $i++;
        }
        return $data;
    }
    public function getFormattedSensorDataOfDay($sid, $day) {
        $data = [];
        //for ($i = 0; $i <= 25; $i++) {
        for ($i = 0; $i <= 97; $i++) {
            $name = sprintf("%02d", $i);
            $time = sprintf("%s  + %d mins", $day, $i*15);
            //$time = sprintf("%s  + %d hours", $day, $i);
            $day2 = date('Y-m-d H:i:s', strtotime($time));
            /*if ($i < 24) {
                $time = sprintf("%s %02d:00:00", $day, $i);
            } else {
                $day2 = date('Y-m-d', strtotime($day . ' + 1 days'));
                $time = sprintf("%s 00:00:00", $day2);
            }*/
            $item = [
                'name' => $name,
                'value' => [$day2, -1]
            ];
            array_push($data, $item);
        }
        $now = now_utc();
        $len = count($data);
        $lastValue = 0;
        for ($i = 0; $i < $len - 1; $i++) {
            $t1 = $data[$i]['value'][0];
            $t2 = $data[$i + 1]['value'][0];
            $day1 = time_local_to_utc($t1);
            $day2 = time_local_to_utc($t2);
            $conditions = [
                'sid' => $sid,
                'oc_time[>=]' => $day1,
                'oc_time[<]' => $day2,
                'ORDER' => ['id' => 'ASC']
            ];
            $sensorData = db_first_sensor_data($conditions);
            if (gettype($sensorData) == 'array' && count($sensorData) > 0) {
                $data[$i]['value'][1] = round($sensorData['value'], 1);
                $lastValue = round($sensorData['value'], 1);
            } else {
                if ($day1 < $now && $lastValue > 0) {
                    $data[$i]['value'][1] = $lastValue;
                } else {
                    $data[$i]['value'][1] = -1;
                }
            }
        }
        $isLast = TRUE;
        for ($i = $len - 1; $i >= 0; $i--) {
            if ($data[$i]['value'][1] < 0) {
                if ($isLast) {
                    array_pop($data);
                } else {
                    $data[$i]['value'][1] = $data[$i + 1]['value'][1];
                }
            } else {
                $isLast = FALSE;
            }
        }
        return $data;
    }
    public function getForecastTempOfDay($buildingId, $day) {
        $data = [];
        $day1 = time_local_to_utc($day);
        $day2 = date('Y-m-d H:i:s', strtotime($day1 . ' + 1 days'));
        $conditions = [
            'building_id' => $buildingId,
            'f_time[>=]' => $day1,
            'f_time[<=]' => $day2,
            'ORDER' => ['f_time' => 'ASC']
        ];
        $weatherList = db_select_weather($conditions);
        foreach ($weatherList as $weather) {
            $time = $weather['f_time'];
            $time = time_utc_to_local($time);
            $temp = (int) $weather['temp'];
            $name = substr($time, 11, 2);
            $item = [
                'name' => $name,
                'value' => [$time, $temp]
            ];
            array_push($data, $item);
        }
        return $data;
    }
    public function getSensor15Min($sid) {
        if ($this->buildingId == '9') {
            $cur = '2018-09-05 15:30:00';
        } else {
            $cur = date('Y-m-d H:i:s');
        }
        $past = date('Y-m-d H:i:s', strtotime($cur . ' -15 minutes'));
        $conditions = [
            'sid' => $sid,
            //'oc_time[>=]' => $past,
            //'oc_time[<=]' => $cur,
            "ORDER" => ["id" => "DESC"],
            "LIMIT" => 2
        ];
        $items = db_sensor_data($conditions);
        $v1 = 26;
        $v2 = 26;
        if ($items) {
            $len = count($items);
            $v2 = round($items[0]['value'], 1);
            $v1 = round($items[$len - 1]['value'], 1);
        }
        return [$v1, $v2];
    }
    public function getForecastWeather($buildingId, $day) {
        global $WEATHER_SUMMARY;
        $weatherIndex = 3;
        foreach ($WEATHER_SUMMARY as $weather) {
            if ($this->hasWeatherSummary($buildingId, $day, $weather['summary'])) {
                $weatherIndex = $weather['index'];
                break;
            }
        }
        return $weatherIndex;
    }
    
    public function hasWeatherSummary($buildingId, $day, $summary) {
        $day1 = time_local_to_utc($day);
        $day2 = date('Y-m-d H:i:s', strtotime($day1 . ' + 1 days'));
        $conditions = [
            'building_id' => $buildingId,
            'f_time[>=]' => $day1,
            'f_time[<]' => $day2,
            'summary[~]' => $summary
        ];
        $summary = db_get_weather_summary($conditions);
        if ($summary) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getForecastTempMaxInOneDay($buildingId, $day) {
        $day1 = time_local_to_utc($day);
        $day2 = date('Y-m-d H:i:s', strtotime($day1 . ' + 1 days'));
        $conditions = [
            'building_id' => $buildingId,
            'f_time[>=]' => $day1,
            'f_time[<]' => $day2,
        ];
        $max = db_get_max_temp($conditions);
        if ($max == null) {
            $max = 0;
        } else {
            $max = (int) $max;
        }
        return $max;
    }
    public function getForecastTempMinInOneDay($buildingId, $day) {
        $day1 = time_local_to_utc($day);
        $day2 = date('Y-m-d H:i:s', strtotime($day1 . ' + 1 days'));
        $conditions = [
            'building_id' => $buildingId,
            'f_time[>=]' => $day1,
            'f_time[<]' => $day2,
        ];
        $min = db_get_min_temp($conditions);
        if ($min == null) {
            $min = 0;
        } else {
            $min = (int) $min;
        }
        return $min;
    }
    public function getCurrentTemperature($buildingId) {
        // Get current weather temperature if it exist.
        $temp = 999;
        // Get forecast weather temperature
        if ($temp > 100) {
            $temp = $this->getForecastNowTemperature($buildingId);
        }
        return (int) $temp;
    }
    
    public function getForecastNowTemperature($buildingId) {
        $now = date('Y-m-d H:i:s');
        $utc = time_local_to_utc($now);
        $conditions = [
            'building_id' => $buildingId,
            'f_time[<=]' => $utc,
            'ORDER' => ['f_time' => 'DESC'],
            'LIMIT' => 1
        ];
        $weather = db_select_weather($conditions);
        if ($weather) {
            $temp = $weather[0]['temp'];
        } else {
            $temp = 999;
        }
        return $temp;
    }
}