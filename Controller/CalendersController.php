<?php

ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class CalendersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
    }

    /*
     * Show calender data
     * Created By: T:307
     * Created Date: 23.02.2017 
     */

    public function index() {
        $this->set('refine_project_sidebar', true);
        $this->loadModel('Task');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->Task->recursive = -1;
        $getTask = [];
        if ($this->request->is('Post') && !empty($this->request->data['filter_project_id'])) {
            $this->set('set_project', $this->data['filter_project_id']);
            $projectid = $this->request->data['filter_project_id'];
            $getTask = $this->Task->find('all', array(
                'conditions' => array('Task.AssignedBy' => $memberId, 'Task.ProjectID' => $projectid),
                'fields' => array('TaskID', 'Name', 'Status', 'StartDate', 'EndDate')
            ));
        } else {
            $getTask = $this->Task->find('all', array(
                'conditions' => array('Task.AssignedBy' => $memberId),
                'fields' => array('TaskID', 'Name', 'Status', 'StartDate', 'EndDate')
            ));
        }

        $taskList = [];
        if (!empty($getTask)) {
            foreach ($getTask as $task) {

                if ($task['Task']['Status'] == 0 || $task['Task']['Status'] == 1) {
                    $status = '0%';
                } else {
                    $status = $task['Task']['Status'];
                }

                $url = Router::url('/schedules/view_task/' . $task['Task']['TaskID']);
                $taskList[] = [
                    'title' => $task['Task']['Name'],
                    'status' => $status,
                    'url' => $url,
                    'start' => $task['Task']['StartDate'],
                ];
            }
        }
        $this->set('taskLists', $taskList);
    }

}
