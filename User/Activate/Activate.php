<?php
/**
 * Author: Derek
 * Date: 2019.02
 */

class Activate extends TeamupBase {
    private $action;
    private $activateCode;
    
    private $return = [
        'success' => true,
        'code' => SUCCESS,
        'msg' => '',
        'data' => []
    ];
    
    public function __construct() {
        //
    }
    
    protected function prepareRequestParams() {
        $this->action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
        $this->activateCode = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
        
        return true;
    }
    
    protected function process() {
        $this->processUserActivate();

        return true;
    }
    
    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }
    
    protected function responseWeb() {
        $GLOBALS['smarty']->assign("ShowMsg", $this->return['msg']);
        $GLOBALS['smarty']->display('activate.tpl');
    }

    private function processUserActivate() {
        $session = db_get_signup_session_by_code($this->activateCode);
        if ($session) {
            db_activate_signup_status($this->activateCode);
            $this->return['msg'] = 'Your account has been activated, please login.';
        } else {
            $this->return['msg'] = 'Activate code doen not exist !';
        }
    }

}
