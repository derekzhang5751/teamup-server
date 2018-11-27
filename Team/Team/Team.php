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
        } else if ($this->action == 'create_team') {
            $this->processCreateTeam();
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
                $this->return['data'] = $entityBody;
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

}