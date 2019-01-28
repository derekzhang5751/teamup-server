<?php
/**
 * User: Derek
 * Date: 2018.09
 */

class TeamupBase extends \Bricker\RequestLifeCircle {

    public function __construct() {
    }
    
    protected function prepareRequestParams() {
        return true;
    }
    
    protected function process() {
        return false;
    }
    
    protected function responseHybrid() {
        exit('Not support !!');
    }
    
    protected function responseWeb() {
        exit('Not support !!');
    }
    
    protected function responseMobile() {
        exit('Not support !!');
    }
    
    protected function getRequestBody() {
        $body = file_get_contents('php://input');
        return $body;
    }
    
}
