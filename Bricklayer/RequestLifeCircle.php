<?php
/**
 * Created by PhpStorm.
 * User: Derek
 * Date: 2018-01-11
 * Time: 2:05 PM
 */

namespace Bricker;


abstract class RequestLifeCircle
{
    protected $db = null;
    protected $log = null;
    protected $trace = false;
    protected $timeBegin = 0;
    protected $timeEnd = 0;
    protected $timeConsuming = 0;


    public function run() {
        global $gConfig;
        $this->trace = $gConfig['trace'];

        if (isset($GLOBALS['db'])) {
            $this->db = $GLOBALS['db'];
        }
        
        if (isset($GLOBALS['log'])) {
            $this->log = $GLOBALS['log'];
        }
        
        $this->traceBegin();
        
        if ($this->prepareRequestParams() === true) {
            if ($this->process() === true) {
                switch ($GLOBALS['DeviceType']) {
                    case DEVICE_HYBRID:
                        $this->responseHybrid();
                        break;
                    case DEVICE_MOBILE:
                        $this->responseMobile();
                        break;
                    default:
                        $this->responseWeb();
                        break;
                }
                $this->traceEnd();
            } else {
                $this->traceEnd();
                exit('Application Error !!');
            }
        } else {
            $this->traceEnd();
            exit('Invalid Request !!');
        }
    }
    
    abstract protected function prepareRequestParams();
    abstract protected function process();
    abstract protected function responseWeb();
    abstract protected function responseHybrid();
    abstract protected function responseMobile();
    
    protected function jsonResponse($result) {
        $data_arr = array(
            'success'    => $result['success'],
            'code'       => $result['code'],
            'msg'        => $result['msg'],
            'data'       => $result['data']
        );
        if (SESSION_ID) {
            $data_arr['SESSION_ID'] = SESSION_ID;
        }
        
        $json = json_encode($data_arr);
        $this->traceEnd();
        exit($json);
    }
    
    protected function traceBegin() {
        if ($this->trace) {
            $this->timeBegin = microtime(true);
        }
    }
    
    protected function traceEnd() {
        if ($this->trace) {
            $this->timeEnd = microtime(true);
            $this->timeConsuming = $this->timeEnd - $this->timeBegin;
            if ($this->log) {
                $this->log->log('trace', sprintf(' [%.3f] %s', $this->timeConsuming, $_SERVER['REQUEST_URI']));
            }
        }
    }
}

