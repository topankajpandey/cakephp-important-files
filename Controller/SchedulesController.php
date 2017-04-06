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

    /*
     * Show calender data
     * Created By: T:307
     * Created Date: 23.02.2017 
     */

    public function calender() {
        $this->loadModel('Task');
        $this->Task->recursive = -1;
        $getTask = $this->Task->find('all', array(
            'conditions' => array('Task.Status' => 1),
            'fields' => array('TaskID', 'Name', 'StartDate', 'EndDate')
        ));
        $taskList = [];
        if (!empty($getTask)) {
            foreach ($getTask as $task) {
                $url = Router::url('/schedules/view_task/' . $task['Task']['TaskID']);
                $taskList[] = [
                    'title' => $task['Task']['Name'],
                    'url' => $url,
                    'start' => $task['Task']['StartDate'],
                ];
            }
        }
        $this->set('taskLists', $taskList);
    }

    public function sc_home() {
        $this->loadModel('Schedule');
        $this->loadModel('Project');
        $this->loadModel('Task');
        $this->Task->recursive = -1;
        if ($this->request->is('Post')) {
            $projectid = base64_decode($this->request->data['p']);
            $getTask = $this->Task->find('all', array(
                'conditions' => array('Task.ProjectID' => $projectid),
                'fields' => array('TaskID', 'Name', 'StartDate', 'EndDate')
            ));
        } else {
            $getTask = $this->Task->find('all', array(
                'conditions' => array('Task.Status' => 1),
                'fields' => array('TaskID', 'Name', 'StartDate', 'EndDate')
            ));
        }
        $taskList = [];
        if (!empty($getTask)) {
            foreach ($getTask as $task) {
                $url = Router::url('/schedules/view_task/' . $task['Task']['TaskID']);
                $taskList[] = [
                    'title' => $task['Task']['Name'],
                    'url' => $url,
                    'start' => $task['Task']['StartDate'],
                    'end' => $task['Task']['EndDate'],
                ];
            }
        }
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
        $MilestoneID = base64_decode($MilestoneID);
        $this->loadModel('Tasks');
        $userDetail = $this->Session->read('Auth.User');
        $userID = $userDetail['MemberID'];
        $memberName = $this->Custom->get_member_name($userDetail);
        if ($this->request->is('Post')) {
            $this->Task->set($this->data);
            if ($this->Task->validates()) {
                $taskArr = $this->request->data['Task'];
                $taskArr['AssignedBy'] = $userID;
                $taskArr['Position'] = $this->GetNextTaskPosition($taskArr['MilestoneID']);
                $taskArr['Status'] = 1;
                $taskArr['CreatedDate'] = date('Y-m-d H:i:s');
                $taskArr['ChangedDate'] = date('Y-m-d H:i:s');

                if (!empty($taskArr['MilestoneID'])) {
                    if ($this->Task->save($taskArr)) {
                        $this->loadModel('TaskHIstory');
                        $NewTaskID = $this->Task->getLastInsertId();

                        //saving task history
                        $taskHistoryArr['TaskHIstory']['TaskID'] = $NewTaskID;
                        $taskHistoryArr['TaskHIstory']['TaskName'] = $taskArr['Name'];
                        $taskHistoryArr['TaskHIstory']['ProjectID'] = $taskArr['MilestoneID'];
                        $taskHistoryArr['TaskHIstory']['ChangedDate'] = date('Y-m-d H:i:s');
                        $taskHistoryArr['TaskHIstory']['ChangedBy'] = $userID;
                        $taskHistoryArr['TaskHIstory']['ChangeCode'] = 'new_task';
                        $taskHistoryArr['TaskHIstory']['NewValue'] = $taskArr['Name'];
                        $this->TaskHIstory->save($taskHistoryArr);

                        //saving project feed
                        $this->loadModel('ProjectFeed');
                        $this->ProjectFeed->create();
                        $this->ProjectFeed->set('ProjectID', $taskArr['MilestoneID']);
                        $this->ProjectFeed->set('ResourceID', $NewTaskID);
                        $this->ProjectFeed->set('Date', date('y-m-d h:i:s'));
                        $this->ProjectFeed->set('FeedVersion', '2');
                        $this->ProjectFeed->set('InitiatorID', $userID);
                        $this->ProjectFeed->set('type', 'new_task');
                        $this->ProjectFeed->set('Title', $memberName . ' has created the task <strong>' . $taskArr['Name'] . '<strong>');
                        $this->ProjectFeed->save();

                        $this->Session->setFlash("The task has been created successfully", 'success_message');
                        $this->redirect(array('controller' => 'dashboard'));
                    }
                } else {
                    $this->Session->setFlash("Please correct the following errors", 'error_message');
                }
            }
        }
        $this->set('MilestoneID', $MilestoneID);
    }

    public function view_task($tid) {

        $this->loadModel('Project');
        $this->loadModel('TaskHistory');
        $this->loadModel('TaskComment');
        $this->loadModel('Member');

        $userDetail = $this->Session->read('Auth.User');
        // echo "<pre>";
        // print_r($userDetail);
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
            //  $this->TaskComment->set('TaskID', $tid);
            $this->TaskComment->set($this->request->data);
            if ($this->TaskComment->save($this->request->data)) {
                // echo "<pre>";
                // print_r($this->request->data); exit;
                $NewTaskCommentID = $this->TaskComment->getLastInsertId();
                //exit;

                $this->Session->setFlash("The task comment has been created successfully", 'success_message');
                //$this->redirect(array('controller' => 'schedules/view_task/'.$tid));
                $this->redirect(array('controller' => 'schedules', 'action' => 'view_task', $tid));
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
            }
        }

        $taskComment = $this->TaskComment->find('all', array('conditions' => array('TaskComment.TaskID' => $tid), 'fields' => array('TaskComment.TaskCommentID', 'TaskComment.Date')));

        $this->set('taskDetalis', $taskDetalis);
        $this->set('projectName', $projectName);
        $this->set('memberName', $memberName);
        $this->set('taskHistory', $taskHistory);
        $this->set('taskComment', $taskComment);
        $this->set('assignedby', $assignedby);
    }

    public function edit_task($tid) {
        $this->loadModel('Schedule');
        $this->loadModel('Project');
        $this->loadModel('Member');
        $this->set('tid', $tid);

        if (!empty($this->data)) {
            $TaskID = $this->data["TaskID"];

            // echo "<pre>";
            // pr($this->request->data);
            // exit;

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
                //$this->redirect(array('controller' => 'projects','action'=>'edit_project',$ProjectID));
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

}
