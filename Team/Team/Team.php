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
}