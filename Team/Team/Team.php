<?php
/**
 * Author: Derek
 * Date: 2018.11
 */
class Team extends TeamupBase {
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
        
        return true;
    }

    protected function process() {
        $this->return['data']['action'] = $this->action;
        if ($this->action == 'team_list') {
            $this->processGetTeamList();
        } else if ($this->action == 'get_team_detail') {
            $this->processGetTeamDetail();
        } else if ($this->action == 'search_team') {
            $this->processSearchTeams();
        } else if ($this->action == 'save_team') {
            $this->processSaveTeam();
        } else if ($this->action == 'apply_team') {
            $this->processApplyTeam();
        } else if ($this->action == 'list_apply') {
            $this->processApplyList();
        } else if ($this->action == 'accept_apply') {
            $this->processAcceptApply();
        } else if ($this->action == 'upload_image') {
            $this->uploadFile();
        } else if ($this->action == 'get_user_follows') {
            return $this->processGetUserFolloes();
        } else if ($this->action == 'get_user_myteams') {
            return $this->processGetUserMyTeams();
        }
        return true;
    }

    protected function responseHybrid() {
        $this->jsonResponse($this->return);
    }

    private function processGetTeamList() {
        $conditions = [
            'ORDER' => ['create_time' => 'DESC'],
        ];
        $teamList = db_select_teams($conditions);
    }

    private function processSaveTeam() {
        $entityBody = $this->getRequestBody();
        $team = json_decode($entityBody, true);
        if ($team) {
            if ($team['id'] > 0) {
                // Update the team
                db_update_team($team);
                $id = $team['id'];
            } else {
                $id = db_insert_team($team);
            }
            if ($id) {
                $this->return['success'] = true;
                $this->return['data']['teamId'] = $id;
            } else {
                $this->return['success'] = false;
                $this->return['msg'] = 'Create the team error!';
                $this->return['data'] = $entityBody;
            }
        } else {
            $this->return['success'] = false;
            $this->return['data'] = $entityBody;
        }
    }

    private function processSearchTeams() {
        $entityBody = $this->getRequestBody();
        $cond = json_decode($entityBody, true);
        if ($cond) {
            $condition = [];
            if ($cond['user_id']) {
                $condition['author'] = $cond['user_id'];
            }
            if ($cond['status']) {
                $condition['status'] = $cond['status'];
            } else {
                $condition['status[>=]'] = 0;
            }
            if ($cond['category']) {
                $condition['category'] = $cond['category'];
            }
            $teams = db_select_teams($condition);
            if ($teams) {
                $this->return['success'] = true;
                $this->return['data']['recommended'] = [];
                $this->return['data']['recent'] = [];
                foreach ($teams as $team) {
                    $brief = [
                        id => $team['id'],
                        author => $team['author'],
                        time_begin => $team['time_begin'],
                        time_end => $team['time_end'],
                        status => $team['status'],
                        title => $team['title'],
                        photo => '/team/photo.jpeg'
                    ];
                    array_push($this->return['data']['recommended'], $brief);
                    array_push($this->return['data']['recent'], $brief);
                }
            } else {
                $this->return['success'] = true;
                $this->return['data'] = [];
            }
        } else {
            $this->return['success'] = false;
            $this->return['data'] = $entityBody;
        }
    }

    private function processGetTeamDetail() {
        $teamId = isset($_REQUEST['teamid']) ? trim($_REQUEST['teamid']) : '0';
        $team = db_get_team_info($teamId);
        if ($team) {
            $this->return['success'] = true;
            $this->return['data']['team'] = $team;
        } else {
            $this->return['success'] = false;
            $this->return['data']['team'] = [];
        }
    }

    private function processApplyTeam() {
        $entityBody = $this->getRequestBody();
        $apply = json_decode($entityBody, true);
        if ($apply) {
            $user = db_get_user_info($apply['user_id']);
            if (!$user) {
                $this->return['success'] = false;
                $this->return['msg'] = $GLOBALS['LANG']['NO_USER'];
                return;
            }
            $team = db_get_team_info($apply['team_id']);
            if (!$team) {
                $this->return['success'] = false;
                $this->return['msg'] = $GLOBALS['LANG']['NO_TEAM'];
                return;
            }

            $status = $apply['status'];
            if ($status > 0) {
                if ($team['need_review'] == 1) {
                    $status = LINK_APPLY;
                } else {
                    $status = LINK_MEMBER;
                }
            } else {
                $status = LINK_SUBSCRIPT;
            }

            if (db_exist_link_user_team($user['id'], $team['id'])) {
                $success = db_update_link_user_team($user['id'], $team['id'], $status, $apply['remark']);
            } else {
                $success = db_insert_link_user_team($user['id'], $team['id'], $status, $apply['remark']);
            }

            if ($success) {
                $this->return['success'] = true;
            } else {
                $this->return['success'] = false;
                $this->return['msg'] = $GLOBALS['LANG']['APPLY_ERROR'];
            }
        } else {
            $this->return['success'] = false;
            $this->return['data'] = $entityBody;
        }
    }

    private function processApplyList() {
        $teamId = isset($_REQUEST['teamid']) ? trim($_REQUEST['teamid']) : '0';
        $users = db_select_apply_user_of_team($teamId);
        $this->return['success'] = true;
        $this->return['data']['teamid'] = $teamId;
        if ($users) {
            $this->return['data']['users'] = $users;
        } else {
            $this->return['data']['users'] = [];
        }
    }

    private function processAcceptApply() {
        $entityBody = $this->getRequestBody();
        $apply = json_decode($entityBody, true);
        if ($apply) {
            $status = $apply['status'];
            if ($status != 1) {
                $this->return['success'] = false;
                $this->return['msg'] = $GLOBALS['LANG']['LOGICAL_ERROR'];
                return;
            }

            $status = LINK_MEMBER;
            $success = db_update_link_user_team($apply['user_id'], $apply['team_id'], $status, $apply['remark']);
            if ($success) {
                $this->return['success'] = true;
            } else {
                $this->return['success'] = false;
                $this->return['msg'] = $GLOBALS['LANG']['APPLY_ERROR'];
            }
        } else {
            $this->return['success'] = false;
            $this->return['data'] = $entityBody;
        }
    }

    private function uploadFile() {
        global $gConfig;
        $target_dir = $gConfig['upload']['uploadpath'];
        $maxSize = $gConfig['upload']['maxsize'];
        //echo print_r($_FILES);
        $buildingId = isset($_REQUEST["buildingid"]) ? trim($_REQUEST["buildingid"]) : '0';
        $sensorId = isset($_REQUEST["sensorid"]) ? trim($_REQUEST["sensorid"]) : '0';
        if (!$this->sensorCheck($sensorId, $buildingId)) {
            $this->return['success'] = false;
            $this->return['msg'] = 'Sensor does not exist.';
            return;
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
        //$target_file = $target_dir . basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $buildingId;
        create_dir($target_file);
        $target_file = $target_file . '/' . $sensorId . '.' . $imageFileType;
        
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
            $this->return['msg'] = 'Sorry, file already exists.';
            $uploadOk = 0;
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
                $picUrl = str_replace($target_dir, "http://iotapi.peakenergypower.com/sensorpic/", $target_file);
                db_update_picurl_of_sensor($sensorId, $picUrl);
            } else {
                $this->return['success'] = false;
                $this->return['msg'] = 'Sorry, there was an error uploading your file.';
            }
        }
    }

    private function processGetUserFolloes() {
        $userId = isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '0';
        $conditions = [
            'author' => $userId
        ];
        $teams = db_select_teams($conditions);
        $this->return['data']['follows'] = [];
        foreach ($teams as $team) {
            $brief = [
                id => $team['id'],
                author => $team['author'],
                time_begin => $team['time_begin'],
                time_end => $team['time_end'],
                status => $team['status'],
                title => $team['title'],
                photo => '/team/photo.jpeg'
            ];
            array_push($this->return['data']['follows'], $brief);
        }
        $this->return['success'] = true;
    }

    private function processGetUserMyTeams() {
        $userId = isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '0';
        $conditions = [
            'author' => $userId
        ];
        $teams = db_select_teams($conditions);
        $this->return['data']['myteams'] = [];
        foreach ($teams as $team) {
            $brief = [
                id => $team['id'],
                author => $team['author'],
                time_begin => $team['time_begin'],
                time_end => $team['time_end'],
                status => $team['status'],
                title => $team['title'],
                photo => '/team/photo.jpeg'
            ];
            array_push($this->return['data']['myteams'], $brief);
        }
        $this->return['success'] = true;
    }

}