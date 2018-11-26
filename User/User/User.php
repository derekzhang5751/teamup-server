<?php
/**
 * Author: Derek
 * Date: 2018.11
 */
class User extends TeamupBase {
    private $action;
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
        if (empty($this->action)) {
            return false;
        } else {
            return true;
        }
    }

    protected function process() {
        $this->return['data']['action'] = $this->action;
        if ($this->action == 'login') {
            return $this->processUserLogin();
        }
        return false;
    }

    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }
    
    private function processUserLogin() {
        $username = isset($_REQUEST['username']) ? trim($_REQUEST['username']) : '';
        $password = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';
        if (empty($username) || empty($password)) {
            return false;
        }
        $user = db_check_user_login($username, $password);
        if ($user) {
            $this->return['success'] = true;
            $this->return['data'] = $user;
        } else {
            $this->return['success'] = false;
            $this->return['data'] = [];
            $this->return['msg'] = $GLOBALS['LANG']['LOGIN_ERROR'];
        }
        return true;
    }
}