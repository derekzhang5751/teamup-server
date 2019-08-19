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
        parent::prepareRequestParams();

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
        } else if ($this->action == 'signup') {
            return $this->processUserSignup();
        } else if ($this->action == 'get_user') {
            return $this->processGetUserProfile();
        } else if ($this->action == 'save_profile') {
            return $this->processSaveProfile();
        } else if ($this->action == 'upload_photo') {
            return $this->processUploadUserPhoto();
        } else if ($this->action == 'change_password') {
            return $this->changePassword();
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
        $entityBody = $this->getRequestBody();
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
                    'password'   => '1234',
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
                if (empty($user['first_name'])) {
                    $d_list = explode("@", $user['email']);
                    $user['first_name'] = $d_list[0];
                }
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

    private function processUserSignup() {
        $username = '';
        $password = '';
        $nameType = '';
        $source = '';
        $entityBody = $this->getRequestBody();
        $signup = json_decode($entityBody, true);
        if ($signup) {
            $username = isset($signup['username']) ? trim($signup['username']) : '';
            $password = isset($signup['password']) ? trim($signup['password']) : '';
            $nameType = isset($signup['name_type']) ? trim($signup['name_type']) : 'email';
            $source = isset($signup['source']) ? trim($signup['source']) : 'moreppl';
        } else {
            return false;
        }

        $user = db_get_user_by_email($username);
        if ($user) {
            $this->return['success'] = false;
            $this->return['data'] = [];
            $this->return['msg'] = $GLOBALS['LANG']['SIGNUP_EMAIL_TAKEN'];
            return true;
        } else {
            $regTime = now_utc();
            $code = md5($regTime);
            $data = [
                'id'          => 0,
                'activate_id' => $code,
                'username'    => $username,
                'password'    => $password,
                'name_type'   => $nameType,
                'reg_time'    => $regTime,
                'status'      => 0
            ];
            $session = db_get_signup_session_by_name($username);
            if ($session) {
                // Update signup session
                $data['id'] = $session['id'];
                db_update_signup_session($data);
            } else {
                // Insert a new signup session
                db_insert_signup_session($data);
            }
            $this->return['success'] = true;
            $this->return['msg'] = '';
            return true;
        }
        return true;
    }

    private function processGetUserProfile() {
        $userId = isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '0';
        $user = db_get_user_info($userId);
        if ($user) {
            $this->return['success'] = true;
            $this->return['data']['user'] = $user;
            if ($user['photo_url']) {
                $this->return['data']['user']['photo_url'] = $user['photo_url'];
            } else {
                $this->return['data']['user']['photo_url'] = '/upload/default/head.svg';
            }
        } else {
            $this->return['success'] = false;
            $this->return['msg'] = 'User does not exist.';
            $this->return['data']['userid'] = $userId;
        }
        return true;
    }

    private function processSaveProfile() {
        $firstName = '';
        $lastName = '';
        $mobile = '';
        $sex = '';
        $birthday = '';
        $desc = '';
        $entityBody = $this->getRequestBody();
        $user = json_decode($entityBody, true);
        if ($user) {
            $firstName = isset($user['first_name']) ? trim($user['first_name']) : '';
            $lastName = isset($user['last_name']) ? trim($user['last_name']) : '';
            $mobile = isset($user['mobile']) ? trim($user['mobile']) : '';
            $sex = isset($user['sex']) ? trim($user['sex']) : '0';
            $birthday = isset($user['birthday']) ? trim($user['birthday']) : '';
            $desc = isset($user['desc']) ? trim($user['desc']) : '';
        } else {
            return false;
        }
        $ret = db_update_user_profile($user);
        if ($ret > 0) {
            $this->return['success'] = true;
            $this->return['data']['user'] = $user;
        } else {
            $this->return['success'] = false;
            $this->return['data'] = [];
            $this->return['msg'] = $GLOBALS['LANG']['SYS_ERROR'];
        }
        return true;
    }

    private function processUploadUserPhoto() {
        global $gConfig;
        $target_dir = $gConfig['upload']['uploadpath'];
        $maxSize = $gConfig['upload']['maxsize'];
        //echo print_r($_FILES);
        $userId = $this->currentUserId; // isset($_REQUEST["userid"]) ? trim($_REQUEST["userid"]) : '0';
        $user = db_get_user_info($userId);
        if (!$user) {
            $this->return['success'] = false;
            $this->return['msg'] = 'User does not exist.';
            return true;
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
        //$target_file = $target_dir . basename($_FILES["file"]["name"]);
        $target_file = $target_dir . 'headphoto';
        create_dir($target_file);
        $target_file = $target_file . '/' . $userId . '.' . $imageFileType;
        
        $uploadOk = 1;
        // Check if image file is a actual image or fake image
        if (isset($_POST["submit"])) {
            $check = getimagesize($_FILES["file"]["tmp_name"]);
            if ($check !== false) {
                //echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                $this->return['msg'] = 'File is NOT an image.';
                $uploadOk = 0;
            }
        }
        // Check if file already exists
        if (file_exists($target_file)) {
            //$this->return['msg'] = 'Sorry, file already exists.';
            //$uploadOk = 0;
            //chmod($target_file, 0755); //Change the file permissions if allowed
            unlink($target_file); //remove the file
        }
        // Check file size
        if ($_FILES["file"]["size"] > $maxSize) {
            $this->return['msg'] = 'Sorry, your file is too large.';
            $uploadOk = 0;
        }
        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $this->return['msg'] = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $this->return['success'] = false;
        } else {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                $this->return['success'] = true;
                $this->return['msg'] = "The file " . basename($_FILES["file"]["name"]) . " has been uploaded.";
                // Update sensor's picture url
                $picUrl = str_replace($target_dir, "/", $target_file);
                $picUrl = '/upload' . $picUrl;
                db_update_photo_of_user($userId, $picUrl);
            } else {
                $this->return['success'] = false;
                $this->return['msg'] = 'Sorry, there was an error uploading your file.';
            }
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

    private function changePassword() {
        $userId = '';
        $oldPass = '';
        $newPass = '';
        $entityBody = $this->getRequestBody();
        $reqData = json_decode($entityBody, true);
        if ($reqData) {
            $userId = isset($reqData['userId']) ? trim($reqData['userId']) : '';
            $oldPass = isset($reqData['oldPass']) ? trim($reqData['oldPass']) : '';
            $newPass = isset($reqData['newPass']) ? trim($reqData['newPass']) : '';
        } else {
            return false;
        }

        $user = db_check_user_available($userId, $oldPass);
        if (!$user) {
            $this->return['success'] = false;
            $this->return['data'] = [];
            $this->return['msg'] = 'Password is not correct.';
            return false;
        }

        $ret = db_update_user_password($userId, $newPass);
        if ($ret > 0) {
            $this->return['success'] = true;
        } else {
            $this->return['success'] = false;
            $this->return['data'] = [];
            $this->return['msg'] = $GLOBALS['LANG']['SYS_ERROR'];
        }
        return true;
    }

}