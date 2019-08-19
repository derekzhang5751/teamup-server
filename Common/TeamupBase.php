<?php
/**
 * User: Derek
 * Date: 2018.09
 */

class TeamupBase extends \Bricker\RequestLifeCircle {
    protected $currentUserId = 0;

    public function __construct() {
    }
    
    protected function prepareRequestParams() {
        // Get current user id from session
        $this->currentUserId = 1;
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
