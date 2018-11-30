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
        } else if ($this->action == 'search_team') {
            $this->processSearchTeams();
        } else if ($this->action == 'create_team') {
            $this->processCreateTeam();
        } else if ($this->action == 'apply_team') {
            $this->processApplyTeam();
        } else if ($this->action == 'list_apply') {
            $this->processApplyList();
        } else if ($this->action == 'accept_apply') {
            $this->processAcceptApply();
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

    private function processCreateTeam() {
        $entityBody = file_get_contents('php://input');
        $team = json_decode($entityBody, true);
        if ($team) {
            $id = db_insert_team($team);
            if ($id) {
                $this->return['success'] = true;
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
        $entityBody = file_get_contents('php://input');
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
                $this->return['data'] = $teams;
            } else {
                $this->return['success'] = true;
                $this->return['data'] = [];
            }
        } else {
            $this->return['success'] = false;
            $this->return['data'] = $entityBody;
        }
    }

    private function processApplyTeam() {
        $entityBody = file_get_contents('php://input');
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
        $entityBody = file_get_contents('php://input');
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

}