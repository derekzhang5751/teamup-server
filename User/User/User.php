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
        $username = '';
        $password = '';
        $devType = '';
        $devUid = '';
        $devModel = '';
        $token = '';
        $entityBody = file_get_contents('php://input');
        $login = json_decode($entityBody, true);
        if ($login) {
            $username = isset($login['username']) ? trim($login['username']) : '';
            $password = isset($login['password']) ? trim($login['password']) : '';
            $devType = isset($login['dev_type']) ? trim($login['dev_type']) : 'Browser';
            $devUid = isset($login['dev_uid']) ? trim($login['dev_uid']) : '';
            $source = isset($login['source']) ? trim($login['source']) : 'moreppl';
            $devModel = isset($login['dev_model']) ? trim($login['dev_model']) : '';
            $token = isset($login['token']) ? trim($login['token']) : '';
        } else {
            return false;
        }

        if ($source == 'moreppl') {
            $user = db_check_user_login($username, $password);
        } else {
            // for google or facebook login
            $user = db_get_user_by_email($email);
            if (!$user) {
                $pieces = explode(" ", trim($login['name']), 2);
                if (array_len($pieces) > 1) {
                    $firstName = $pieces[0];
                    $lastName = $pieces[1];
                } else {
                    $firstName = $pieces[0];
                    $lastName = '';
                }
                $data = [
                    'username'   => $username,
                    'level'      => 0,
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $username,
                    'mobile'     => '',
                    'sex'        => 0,
                    'birthday'   => '',
                    'is_active'  => 1,
                    'reg_time'   => now_utc(),
                    'desc'       => '',
                    'photo_url'  => trim($login['image']),
                    'source'     => trim($login['source'])
                ];
                db_insert_user($data);
                $user = db_get_user_by_email($email);
            }
        }

        if ($user) {
            $sessionId = $this->updateUserSession($user['id'], $devUid, $devType, $devModel, $token);
            if ($sessionId) {
                $this->return['success'] = true;
                $this->return['data']['user'] = $user;
                $this->return['data']['session'] = $sessionId;
            } else {
                $this->return['success'] = false;
                $this->return['data'] = [];
                $this->return['msg'] = $GLOBALS['LANG']['SYS_ERROR'];
            }
        } else {
            $this->return['success'] = false;
            $this->return['data'] = [];
            $this->return['msg'] = $GLOBALS['LANG']['LOGIN_ERROR'];
        }
        return true;
    }

    private function updateUserSession($userId, $devUid, $devType, $devModel, $token) {
        $sessionId = uniqid();
        if (empty($devUid)) {
            $devUid = $sessionId;
        }
        $session = db_get_user_session2($userId, $devUid);
        if ($session) {
            // Update session id
            $session['session_id'] = $sessionId;
            $success = db_update_user_session($session);
            if ($success <= 0) {
                $success = false;
            }
        } else {
            // New session
            $data = array(
                'user_id'    => $userId,
                'last_time'  => now_utc(),
                'session_id' => $sessionId,
                'dev_uid'    => $devUid,
                'dev_type'   => $devType,
                'dev_model'  => $devModel,
                'token'      => $token
            );
            $success = db_insert_user_session($data);
        }
        if ($success) {
            return $sessionId;
        } else {
            return false;
        }
    }

}