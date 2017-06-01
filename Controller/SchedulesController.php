<?php

ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class SchedulesController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('login', 'signup', 'forgot', 'reset', 'verification'));
    }

    public function sc_home() {
        $this->set('refine_project_sidebar', true);
        $this->loadModel('Schedule');
        $this->loadModel('Project');
        $this->loadModel('Task');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->Task->recursive = -1;
        if ($this->request->is('Post') && !empty($this->request->data['filter_project_id'])) {
            $this->set('set_project', $this->data['filter_project_id']);
            $projectid = $this->request->data['filter_project_id'];
            $getTask = $this->Task->find('all', array(
                'contain' => ['Project'],
                'conditions' => array('Task.ProjectID' => $projectid, 'Task.AssignedBy' => $memberId),
                'fields' => array('TaskID', 'Name', 'Status', 'StartDate', 'EndDate')
            ));
        } else {
            $getTask = $this->Task->find('all', array(
                'contain' => ['Project'],
                'conditions' => array('Task.AssignedBy' => $memberId),
                'fields' => array('TaskID', 'Name', 'Status', 'StartDate', 'EndDate')
            ));
            $projectid = 0;
        }
        
        $taskList = [];
        if (!empty($getTask)) {
            foreach ($getTask as $task) {
                if (!empty($task['Project'])) {
                    $url = Router::url('/schedules/view_task/' . $task['Task']['TaskID']);
                    $findDateFrom = date('Y/m/d', strtotime($task['Task']['StartDate']));
                    $new_date_format_from = strtotime($findDateFrom) * 1000;
                    $findDateTo = date('Y/m/d', strtotime($task['Task']['EndDate']));
                    $new_date_format_to = strtotime($findDateTo) * 1000;
                    
                    if($task['Task']['Status']==0 || $task['Task']['Status']==1){
                        $status = '0%';
                    }else{
                        $status = $task['Task']['Status'];
                    }
                    
                    $taskList[] = array(
                        'name' => $task['Task']['Name'],
                        'values' => [array(
                        'label' => '<a href="' . Router::url('/', true) . 'schedules/view_task/' . $task['Task']['TaskID'] . '">' . $task['Task']['Name'] . '</a>',
                        'customClass' => 'ganttOrange',
                        'desc' => $status,
                        'from' => '/Date(' . $new_date_format_from . ')/',
                        'to' => '/Date(' . $new_date_format_to . ')/'
                    )]);
                }
            }
        }
        $this->set('selected_project_id', $projectid);
        $this->set('taskLists', $taskList);
    }

    function GetNextTaskPosition($ProjectID) {
        $this->loadModel('Task');
        $taskArr = $this->Task->find('first', array(
            'conditions' => array('Task.ProjectID' => $ProjectID),
            'fields' => array('MAX(Task.Position) as Position')
        ));
        return ($taskArr[0]['Position'] + 1);
    }

    public function new_task($MilestoneID = null) {
        //$MilestoneID = base64_decode($MilestoneID);
        $this->loadModel('Tasks');
        $userDetail = $this->Session->read('Auth.User');
        $userID = $userDetail['MemberID'];
        $memberName = $this->Custom->get_member_name($userDetail);
        if ($this->request->is('Post')) {
            $this->Task->set($this->data);
            if ($this->Task->validates()) {

                $taskArr = $this->request->data['Task'];

                $taskArr['AssignedBy'] = $userID;
                $taskArr['Position'] = $this->GetNextTaskPosition($taskArr['ProjectID']);
                $taskArr['Status'] = 1;
                $taskArr['CreatedDate'] = date('Y-m-d H:i:s');
                $taskArr['ChangedDate'] = date('Y-m-d H:i:s');

                if (!empty($taskArr['ProjectID'])) {
                    if ($this->Task->save($taskArr)) {
                        $this->loadModel('TaskHistory');
                        $NewTaskID = $this->Task->getLastInsertId();

                        //saving task history
                        $taskHistoryArr['TaskHistory']['TaskID'] = $NewTaskID;
                        $taskHistoryArr['TaskHistory']['TaskName'] = $taskArr['Name'];
                        $taskHistoryArr['TaskHistory']['ProjectID'] = $taskArr['ProjectID'];
                        $taskHistoryArr['TaskHistory']['ChangedDate'] = date('Y-m-d H:i:s');
                        $taskHistoryArr['TaskHistory']['ChangedBy'] = $userID;
                        $taskHistoryArr['TaskHistory']['ChangeCode'] = 'new_task';
                        $taskHistoryArr['TaskHistory']['NewValue'] = $taskArr['Name'];
                        $this->TaskHistory->save($taskHistoryArr);

                        //saving project feed
                        $this->loadModel('ProjectFeed');
                        $this->ProjectFeed->create();
                        $this->ProjectFeed->set('ProjectID', $taskArr['ProjectID']);
                        $this->ProjectFeed->set('ResourceID', $NewTaskID);
                        $this->ProjectFeed->set('Date', date('y-m-d h:i:s'));
                        $this->ProjectFeed->set('FeedVersion', '2');
                        $this->ProjectFeed->set('InitiatorID', $userID);
                        $this->ProjectFeed->set('type', 'new_task');
                        $this->ProjectFeed->set('Title', $memberName . ' has created the task <strong>' . $taskArr['Name'] . '<strong>');
                        $this->ProjectFeed->save();

                        $this->Session->setFlash("The task has been created successfully", 'success_message');
                        $this->redirect(array('controller' => 'schedules', 'action' => 'sc_home'));
                    }
                } else {
                    $this->Session->setFlash("Please correct the following errors", 'error_message');
                }
            }
        }
        //$this->set('MilestoneID', $MilestoneID);
        $projectList = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectList', $projectList);
    }

    public function edit_task($MilestoneID = null) {
        $this->loadModel('Tasks');
        $userDetail = $this->Session->read('Auth.User');
        $userID = $userDetail['MemberID'];
        $memberName = $this->Custom->get_member_name($userDetail);
        if (!empty($this->data)) {
            $this->Task->set($this->data);
            if ($this->Task->validates()) {
                $taskArr = $this->request->data['Schedule'];
                $taskArr['ChangedDate'] = date('Y-m-d H:i:s');
                $taskArr['AssignedBy'] = $userID;
                if ($this->Task->save($taskArr)) {
                    $this->loadModel('TaskHistory');
                    //saving task history
                    $taskHistoryArr['TaskHistory']['TaskID'] = $MilestoneID;
                    $taskHistoryArr['TaskHistory']['TaskName'] = $taskArr['Name'];
                    $taskHistoryArr['TaskHistory']['ProjectID'] = $taskArr['ProjectID'];
                    $taskHistoryArr['TaskHistory']['ChangedDate'] = date('Y-m-d H:i:s');
                    $taskHistoryArr['TaskHistory']['ChangedBy'] = $userID;
                    $taskHistoryArr['TaskHistory']['ChangeCode'] = 'new_task';
                    $taskHistoryArr['TaskHistory']['NewValue'] = $taskArr['Name'];
                    $this->TaskHistory->save($taskHistoryArr);

                    //saving project feed
                    $this->loadModel('ProjectFeed');
                    $this->ProjectFeed->create();
                    $this->ProjectFeed->set('ProjectID', $taskArr['ProjectID']);
                    $this->ProjectFeed->set('ResourceID', $MilestoneID);
                    $this->ProjectFeed->set('Date', date('y-m-d h:i:s'));
                    $this->ProjectFeed->set('FeedVersion', '2');
                    $this->ProjectFeed->set('InitiatorID', $userID);
                    $this->ProjectFeed->set('type', 'update_task');
                    $this->ProjectFeed->set('Title', $memberName . ' has updated the task <strong>' . $taskArr['Name'] . '<strong>');
                    $this->ProjectFeed->save();

                    $this->Session->setFlash("The task has been updated successfully", 'success_message');
                    $this->redirect(array('controller' => 'schedules', 'action' => 'view_task', $MilestoneID));
                }
            }
        }
        $this->set('MilestoneID', $MilestoneID);
        $this->data = $this->Schedule->find('first', array('conditions' => array('Schedule.TaskID' => $MilestoneID)));
    }

    public function view_task($tid) {

        $this->loadModel('Project');
        $this->loadModel('TaskHistory');
        $this->loadModel('TaskComment');
        $this->loadModel('Member');

        $userDetail = $this->Session->read('Auth.User');

        $userID = $userDetail['MemberID'];
        $firstName = $userDetail['FirstName'];
        $lastName = $userDetail['LastName'];

        $assignedby = $userDetail['FirstName'] . " " . $userDetail['LastName'];

        $taskDetalis = $this->Schedule->find('first', array('conditions' => array('Schedule.TaskID' => $tid)));
        $projectid = $this->Schedule->find('first', array('conditions' => array('Schedule.TaskID' => $tid), 'fields' => array('Schedule.ProjectID')));
        $pid = $projectid['Schedule']['ProjectID'];
        $projectName = $this->Project->find('first', array('conditions' => array('Project.ProjectID' => $pid)));

        $memberid = $this->Schedule->find('first', array('conditions' => array('Schedule.TaskID' => $tid), 'fields' => array('Schedule.AssignedTo')));
        $mid = $memberid['Schedule']['AssignedTo'];
        $memberName = $this->Member->find('first', array('conditions' => array('Member.MemberID' => $mid)));

        $taskHistory = $this->TaskHistory->find('all', array('conditions' => array('TaskHistory.TaskID' => $tid), 'fields' => array('TaskHistory.TaskName', 'TaskHistory.ChangedDate')));

        if ($this->request->is('Post')) {

            $comment = $this->request->data['Comment'];
            $this->request->data['TaskID'] = $tid;
            $this->request->data['Date'] = date('Y-m-d');
            $this->request->data['PostedBy'] = $userID;
            $this->TaskComment->set($this->request->data);
            if ($this->TaskComment->save($this->request->data)) {
                $NewTaskCommentID = $this->TaskComment->getLastInsertId();
                $this->Session->setFlash("The task comment has been created successfully", 'success_message');
                $this->redirect(array('controller' => 'schedules', 'action' => 'view_task', $tid));
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
            }
        }

        $taskComment = $this->TaskComment->find('all', array('conditions' => array('TaskComment.TaskID' => $tid), 'fields' => array('TaskComment.TaskCommentID', 'TaskComment.Comment', 'TaskComment.Date')));

        $this->set('taskDetalis', $taskDetalis);
        $this->set('projectName', $projectName);
        $this->set('memberName', $memberName);
        $this->set('taskHistory', $taskHistory);
        $this->set('taskComment', $taskComment);
        $this->set('assignedby', $assignedby);
    }

    public function comment_task($tid) {
        $this->loadModel('TaskComment');
        if ($this->request->is('Post')) {

            $commentid = $this->request->data['TaskCommentID'];
            // $assignedto = $this->request->data['AssignedTo'];
            // $this->Schedule->set('ProjectID', $projectid);
            // $this->Schedule->set('AssignedTo', $assignedto);
            // $this->Schedule->set($this->request->data);

            if ($this->TaskComment->save($this->request->data)) {
                // echo "<pre>";
                // print_r($this->request->data); exit;
                $NewTaskCommentID = $this->Schedule->getLastInsertId();
                //exit;

                $this->Session->setFlash("The task comment has been created successfully", 'success_message');
                $this->redirect(array('controller' => 'view_task'));
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
            }
        }
    }

    public function delete_task($tid) {
        $this->Schedule->query("DELETE FROM Tasks WHERE TaskID = '" . $tid . "'");
        //$TaskName = $this->Schedule->find('first',array('conditions'=>array('Schedule.TaskID'=>$tid)));
        //print_r($TaskName['Schedule']['Name']);
        $this->Session->setFlash("Task has been removed from the project.", 'success_message');
        $this->redirect(array('controller' => 'schedules', 'action' => 'sc_home'));
    }

    public function delete_taskcomment($tcid, $tid) {
        $this->loadModel('TaskComment');
        // $taskccommentid = $this->TaskComment->find('first',array('conditions'=>array('TaskComment.TaskID'=>$tid), 'fields'=>array('TaskComment.TaskCommentID')));
        // $tcid = $taskccommentid['TaskComment']['TaskCommentID'];
        //  print_r($taskccommentid); die;
        $this->TaskComment->query("DELETE FROM TaskComments WHERE TaskID = '" . $tid . "' and TaskCommentID = '" . $tcid . "'");
        //$TaskName = $this->Schedule->find('first',array('conditions'=>array('Schedule.TaskID'=>$tid)));
        //print_r($TaskName['Schedule']['Name']);
        $this->Session->setFlash("Comment has been removed from the project.", 'success_message');
        $this->redirect(array('controller' => 'schedules', 'action' => 'view_task', $tid));
    }

    /* public function edit_task($tid) {
      $this->loadModel('Schedule');
      $this->loadModel('Project');
      $this->loadModel('Member');
      $this->set('tid', $tid);

      if (!empty($this->data)) {
      $TaskID = $this->data["TaskID"];
      $this->request->data['Schedule']['TaskID'] = $this->data["TaskID"];
      $this->request->data['Schedule']['ProjectID'] = $this->request->data['projectname'];
      $this->request->data['Schedule']['AssignedTo'] = $this->request->data['AssignedTo'];
      $this->request->data['Schedule']['StartDate'] = $this->request->data['Schedule']['CreatedDate'];
      $this->request->data['Schedule']['EndDate'] = $this->request->data['Schedule']['ChangedDate'];
      $this->request->data['Schedule']['Status'] = $this->request->data['Status'];
      $taskdetail = $this->Schedule->find('first', array('conditions' => array('Schedule.TaskID' => $tid), 'fields' => array('Schedule.Name', 'Schedule.ProjectID', 'Schedule.StartDate', 'Schedule.EndDate', 'Schedule.AssignedTo', 'Schedule.Status')));
      $oldname = $taskdetail['Schedule']['Name'];
      $oldprojectname = $taskdetail['Schedule']['ProjectID'];
      $oldstartdate = $taskdetail['Schedule']['StartDate'];
      $oldenddate = $taskdetail['Schedule']['EndDate'];
      $oldassignedto = $taskdetail['Schedule']['AssignedTo'];
      $oldstatus = $taskdetail['Schedule']['Status'];

      $newname = $this->request->data['Schedule']['Name'];
      $newprojectname = $this->request->data['Schedule']['ProjectID'];
      $newstartdate = $this->request->data['Schedule']['CreatedDate'];
      $newenddate = $this->request->data['Schedule']['ChangedDate'];
      $newassignedto = $this->request->data['Schedule']['AssignedTo'];
      $newstatus = $this->request->data['Status'];
      // echo "old: "; print_r($oldstatus['Schedule']['Status']);
      // echo "new: "; print_r($newstatus);
      $pid = $this->request->data['Schedule']['ProjectID'];
      $this->Schedule->set($this->request->data);
      //print_r($this->request->data);exit;

      if ($this->Schedule->save($this->request->data)) {

      if ($oldname != $newname) {

      $this->loadModel('TaskHistory');
      $this->TaskHistory->create();
      $this->TaskHistory->set('ChangedDate', date("y-m-d h:i:s"));
      $this->TaskHistory->set('TaskID', $tid);
      $this->TaskHistory->set('ProjectID', $pid);
      $this->TaskHistory->set('TaskName', 'Changed Task name');
      $this->TaskHistory->set('OldValue', $oldname);
      $this->TaskHistory->set('NewValue', $newname);
      $this->TaskHistory->save();
      }


      if ($oldprojectname != $newprojectname) {

      $this->loadModel('TaskHistory');
      $this->TaskHistory->create();
      $this->TaskHistory->set('ChangedDate', date("y-m-d h:i:s"));
      $this->TaskHistory->set('TaskID', $tid);
      $this->TaskHistory->set('ProjectID', $pid);
      $this->TaskHistory->set('TaskName', 'Changed Project name');
      $this->TaskHistory->set('OldValue', $oldprojectname);
      $this->TaskHistory->set('NewValue', $newprojectname);
      $this->TaskHistory->save();
      }


      if ($oldstartdate != $newstartdate) {

      $this->loadModel('TaskHistory');
      $this->TaskHistory->create();
      $this->TaskHistory->set('ChangedDate', date("y-m-d h:i:s"));
      $this->TaskHistory->set('TaskID', $tid);
      $this->TaskHistory->set('ProjectID', $pid);
      $this->TaskHistory->set('TaskName', 'Changed Task start date');
      $this->TaskHistory->set('OldValue', $oldstartdate);
      $this->TaskHistory->set('NewValue', $newstartdate);
      $this->TaskHistory->save();
      }

      if ($oldenddate != $newenddate) {

      $this->loadModel('TaskHistory');
      $this->TaskHistory->create();
      $this->TaskHistory->set('ChangedDate', date("y-m-d h:i:s"));
      $this->TaskHistory->set('TaskID', $tid);
      $this->TaskHistory->set('ProjectID', $pid);
      $this->TaskHistory->set('TaskName', 'Changed Task end date');
      $this->TaskHistory->set('OldValue', $oldenddate);
      $this->TaskHistory->set('NewValue', $newenddate);
      $this->TaskHistory->save();
      }


      if ($oldassignedto != $newassignedto) {

      $this->loadModel('TaskHistory');
      $this->TaskHistory->create();
      $this->TaskHistory->set('ChangedDate', date("y-m-d h:i:s"));
      $this->TaskHistory->set('TaskID', $tid);
      $this->TaskHistory->set('ProjectID', $pid);
      $this->TaskHistory->set('TaskName', 'Changed Assigned member');
      $this->TaskHistory->set('OldValue', $oldassignedto);
      $this->TaskHistory->set('NewValue', $newassignedto);
      $this->TaskHistory->save();
      }


      if ($oldstatus != $newstatus) {

      $this->loadModel('TaskHistory');
      $this->TaskHistory->create();
      $this->TaskHistory->set('ChangedDate', date("y-m-d h:i:s"));
      $this->TaskHistory->set('TaskID', $tid);
      $this->TaskHistory->set('ProjectID', $pid);
      $this->TaskHistory->set('TaskName', 'Changed status');
      $this->TaskHistory->set('OldValue', $oldstatus);
      $this->TaskHistory->set('NewValue', $newstatus);
      $this->TaskHistory->save();
      }


      $this->Session->setFlash("The task has been updated", 'success_message');
      $this->redirect(array('controller' => 'schedules', 'action' => 'view_task', $TaskID));
      // $this->redirect(array('controller' => 'projects','action'=>'pm_home',$ProjectID));
      } else {
      $this->Session->setFlash("Please correct the following errors", 'error_message');
      }
      } else {
      $this->data = $this->Schedule->find('first', array('conditions' => array('Schedule.TaskID' => $tid)));
      $projectid = $this->Schedule->find('first', array('conditions' => array('Schedule.TaskID' => $tid), 'fields' => array('Schedule.ProjectID')));
      $pid = $projectid['Schedule']['ProjectID'];
      $projectName = $this->Project->find('first', array('conditions' => array('Project.ProjectID' => $pid)));

      $memberid = $this->Schedule->find('first', array('conditions' => array('Schedule.TaskID' => $tid), 'fields' => array('Schedule.AssignedTo')));
      $mid = $memberid['Schedule']['AssignedTo'];
      $memberName = $this->Member->find('first', array('conditions' => array('Member.MemberID' => $mid)));

      $this->set('projectName', $projectName);
      $this->set('memberName', $memberName);
      }
      } */
}
