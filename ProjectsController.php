<?php

ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
CONST STATUS_CODE = 201;
CONST VERSION = 1;

class ProjectsController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');

    public function beforeFilter() {

        parent::beforeFilter();
        $this->Auth->allow(array('login', 'signup', 'forgot', 'reset', 'verification'));
    }

   
    /* ADMIN FUNCTIONS STARTS */
    public function admin_list_project() {
        $this->get_authorize('projects/list_project');
        $this->loadModel('Project');
        $this->Project->recursive = 0;
        if ($this->request->is('post')) {
            $keyword = trim($this->request->data['query']);
            if (!empty($keyword)) {
                @$records = $this->Project->find('all', array('conditions' => array("Project.Name LIKE" => "%$keyword%")));
            }
            $this->set("project", @$records, $this->paginate());
            if (count(@$records) == 0) {
                $this->Session->setFlash("No Record found");
            }
        } else {
            $this->paginate = array('order' => array('Project.ProjectID' => 'ASC'), 'limit' => 20);
            $this->set('project', $this->paginate());
        }
    }

    public function admin_delete_project($id = null) {
        $this->get_authorize('projects/delete_project');
        $this->autoRender = false;
        $this->loadModel('Project');
        $this->Project->id = $id;
        if (!$this->Project->exists()) {
            throw new NotFoundException(__('Invalid Project'));
        }
        if ($this->Project->delete($id, true)) {
            $this->Session->setFlash(__('Project deleted successfully...'));
            $this->redirect(array('action' => 'list_project'));
        }
        $this->Session->setFlash(__('Project details not deleted.Please try again.'));
        $this->redirect(array('action' => 'list_project'));
    }

    public function admin_project_detail($ProjectID) {
        //$this->layout = 'admin/dashboard';
        $this->loadModel('Schedule');
        $this->loadModel('Project');
        $this->loadModel('Task');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->Project->contain(array('ProjectMember' => array('User'), 'ProjectManager' => array('User'), 'ProjectFeed' => array('User')));
        $projectData = $this->Custom->get_projects('first', $ProjectID, ['ProjectID', 'Name', 'Description', 'Archived', 'completed', 'Deleted']);
        $taskLists = $this->Schedule->find('all', array('conditions' => array('Schedule.ProjectID' => $ProjectID), 'fields' => array('Schedule.ProjectID', 'Schedule.TaskID', 'Schedule.Name', 'Schedule.StartDate', 'Schedule.EndDate')));
        $projecttimeduration = $this->Project->find('all', array('conditions' => array('Project.ProjectID' => $ProjectID), 'fields' => array('Project.CreatedDate', 'Project.ChangedDate')));

        $memberIDS = array();
        foreach ($projectData['ProjectManager'] as $proManager) {
            $memberIDS[] = $proManager['MemberID'];
        }
        $this->set('projectData', $projectData);
        $this->set('memberIDS', $memberIDS);
        $this->set('memberId', $memberId);
        $this->set('taskLists', $taskLists);
        $this->set('projecttimeduration', $projecttimeduration);
    }

    public function admin_add_project() {
        $this->get_authorize('projects/add_project');
        $this->loadModel('Project');
    }

    public function admin_update_project($id = null) {
        $this->get_authorize('projects/update_project');
        $this->loadModel('Project');
        $this->set('pid', $id);
        $this->data = $this->Project->find('first', array('conditions' => array('Project.ProjectID' => $id)));
    }

    /* ADMIN FUNCTIONS ENDS */

    public function new_project() {
        if ($this->request->is('Post')) {
            $memberId = $this->Session->read('Auth.User.MemberID');
            $memberName = $this->Session->read('Auth.User.FirstName') . ' ' . $this->Session->read('Auth.User.LastName');
            $this->request->data['Project']['CreatedDate'] = date('y-m-d h:i:s');
            $this->request->data['Project']['ChangedDate'] = date('y-m-d h:i:s');
            //$this->request->data['User']['MemberID'] = $memberId;
            $this->Project->set($this->request->data);
            if ($this->Project->save($this->request->data)) {
                $NewProjectID = $this->Project->getLastInsertId();
                //exit;
                // Specify current user as project manager, since they created the project
                $this->loadModel('ProjectManager');
                $this->ProjectManager->create();
                $this->ProjectManager->set('AddedDate', date('y-m-d h:i:s'));
                $this->ProjectManager->set('ProjectID', $NewProjectID);
                $this->ProjectManager->set('MemberID', $memberId);
                $this->ProjectManager->save();

                // Enter current user as project participant
                $this->loadModel('ProjectMember');
                $this->ProjectMember->create();
                $this->ProjectMember->set('AddedDate', date('y-m-d h:i:s'));
                $this->ProjectMember->set('ProjectID', $NewProjectID);
                $this->ProjectMember->set('MemberID', $memberId);
                $this->ProjectMember->save();

                // Create the preferences for this project and user.  Use the db default values.
                $this->loadModel('SettingsUser');
                $this->SettingsUser->create();
                $this->SettingsUser->set('ProjectID', $NewProjectID);
                $this->SettingsUser->set('MemberID', $memberId);
                $this->SettingsUser->save();

                // Create the project preferences for this project
                $this->loadModel('SettingsProject');
                $this->SettingsProject->create();
                $this->SettingsProject->set('ProjectID', $NewProjectID);
                $this->SettingsProject->set('MemberID', $memberId);
                $this->SettingsProject->save();

                // Create the first document folder, called 'Documents'
                $this->loadModel('Folder');
                $this->Folder->create();
                $this->Folder->set('ProjectID', $NewProjectID);
                $this->Folder->set('CreatedDate', date('y-m-d h:i:s'));
                $this->Folder->set('CreatedBy', $memberId);
                $this->Folder->set('Name', 'Documents');
                $this->Folder->save();

                // Enter feed
                $this->loadModel('ProjectFeed');
                $this->ProjectFeed->create();
                $this->ProjectFeed->set('ProjectID', $NewProjectID);
                $this->ProjectFeed->set('ResourceID', $NewProjectID);
                $this->ProjectFeed->set('Date', date('y-m-d h:i:s'));
                $this->ProjectFeed->set('FeedVersion', '2');
                $this->ProjectFeed->set('InitiatorID', $memberId);
                $this->ProjectFeed->set('type', 'new_project');
                $this->ProjectFeed->set('Title', $memberName . ' has created the project ' . $this->data['Project']['Name']);
                $this->ProjectFeed->save();

                $this->Session->setFlash("The project has been created successfully", 'success_message');
                if ($this->data["prefix"] == 'admin') {
                    $this->redirect(array('action' => 'list_project', 'admin' => true));
                } else {
                    $this->redirect(array('controller' => 'dashboard'));
                }
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
            }
        }
    }

    public function pm_home($ProjectID) {
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->Project->contain(array('ProjectMember' => array('User'), 'ProjectManager' => array('User'), 'ProjectFeed' => array('User')));
        //$projectData = $this->Project->find('first', array('conditions' => array('Project.ProjectID' => $ProjectID)));
        $projectData = $this->Custom->get_projects('first', $ProjectID, ['ProjectID', 'Name', 'Description', 'Archived', 'completed', 'Deleted']);


        $memberIDS = array();
        foreach ($projectData['ProjectManager'] as $proManager) {
            $memberIDS[] = $proManager['MemberID'];
        }
        $this->set('projectData', $projectData);
        $this->set('memberIDS', $memberIDS);
        $this->set('memberId', $memberId);
    }

    public function edit_project($pid) {

        $MemberName = $this->Session->read('Auth.User.FirstName') . ' ' . $this->Session->read('Auth.User.LastName');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->set('pid', $pid);
        if (!empty($this->data)) {
            $ProjectID = $this->data["ProjectID"];
            $OldProjectName = $this->data["OldProjectName"];
            $OldProjectDescription = $this->data["OldProjectDescription"];
            $NewProjectName = $this->data['Project']["Name"];
            $NewProjectDescription = $this->data['Project']["Description"];

            $this->request->data['Project']['ChangedDate'] = date('y-m-d h:i:s');
            $this->request->data['Project']['ProjectID'] = $this->data["ProjectID"];
            $this->Project->set($this->request->data);

            if ($this->Project->save($this->request->data)) {

                if ($OldProjectName != $NewProjectName) {
                    $this->loadModel('ProjectFeed');
                    $comment = $MemberName . ' has changed the project name from ' . $OldProjectName . ' to ' . $NewProjectName;
                    $this->ProjectFeed->create();
                    $this->ProjectFeed->set('Date', date("y-m-d h:i:s"));
                    $this->ProjectFeed->set('FeedVersion', 2);
                    $this->ProjectFeed->set('ProjectID', $ProjectID);
                    $this->ProjectFeed->set('InitiatorID', $memberId);
                    $this->ProjectFeed->set('type', 'edit_project_name');
                    $this->ProjectFeed->set('ResourceID', $ProjectID);
                    $this->ProjectFeed->set('OldValue', $OldProjectName);
                    $this->ProjectFeed->set('NewValue', $NewProjectName);
                    $this->ProjectFeed->set('Title', $comment);
                    $this->ProjectFeed->save();
                }
                if ($OldProjectDescription != $NewProjectDescription) {
                    $this->loadModel('ProjectFeed');
                    $comment = $MemberName . ' has changed the project description for the project ' . $NewProjectName;
                    $this->ProjectFeed->create();
                    $this->ProjectFeed->set('Date', date("y-m-d h:i:s"));
                    $this->ProjectFeed->set('FeedVersion', 2);
                    $this->ProjectFeed->set('ProjectID', $ProjectID);
                    $this->ProjectFeed->set('InitiatorID', $memberId);
                    $this->ProjectFeed->set('type', 'edit_project_description');
                    $this->ProjectFeed->set('ResourceID', $ProjectID);
                    $this->ProjectFeed->set('OldValue', $OldProjectName);
                    $this->ProjectFeed->set('NewValue', $NewProjectName);
                    $this->ProjectFeed->set('Title', $comment);
                    $this->ProjectFeed->save();
                }

                $this->Session->setFlash("The project has been updated", 'success_message');


                if ($this->data["prefix"] == 'admin') {
                    $this->redirect(array('action' => 'list_project', 'admin' => true));
                } else {
                    $this->redirect(array('controller' => 'projects', 'action' => 'pm_home', $ProjectID));
                }
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
                //$this->redirect(array('controller' => 'projects','action'=>'edit_project',$ProjectID));
            }
        } else {
            $this->data = $this->Project->find('first', array('conditions' => array('Project.ProjectID' => $pid)));
        }
    }

    public function edit_pms($ProjectID) {
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->Project->contain(array('ProjectMember' => array('User'), 'ProjectManager' => array('User')));
        $projectData = $this->Project->find('first', array('conditions' => array('Project.ProjectID' => $ProjectID)));
        $memberIDS = array();
        foreach ($projectData['ProjectManager'] as $proManager) {
            $memberIDS[] = $proManager['MemberID'];
        }
        $this->set('projectData', $projectData);
        $this->set('memberIDS', $memberIDS);
        $this->set('memberId', $memberId);
    }

    public function remove_pm($pID, $memberID) {
        $loggedINUserID = $this->Session->read('Auth.User.MemberID');
        $this->loadModel('ProjectManager');
        $this->ProjectManager->query("DELETE FROM ProjectManagers WHERE MemberID = '" . $memberID . "' AND ProjectID = '" . $pID . "' LIMIT 1");

        $this->loadModel('ProjectFeed');
        $loggedInUserName = $this->GetMemberName($loggedINUserID);
        $EditUserName = $this->GetMemberName($memberID);
        $comment = $loggedInUserName . " has removed " . $EditUserName . " as a project manager from the project " . $this->GetProjectName($pID);
        $this->ProjectFeed->create();
        $this->ProjectFeed->set('Date', date("y-m-d h:i:s"));
        $this->ProjectFeed->set('FeedVersion', 2);
        $this->ProjectFeed->set('ProjectID', $pID);
        $this->ProjectFeed->set('InitiatorID', $loggedINUserID);
        $this->ProjectFeed->set('type', 'remove_pm');
        $this->ProjectFeed->set('ResourceID', $pID);
        $this->ProjectFeed->set('ResourceID2', $memberID);
        $this->ProjectFeed->set('Title', $comment);
        $this->ProjectFeed->save();
        $this->Session->setFlash("" . $EditUserName . " has been removed as a project manager.", 'success_message');
        $this->redirect(array('controller' => 'projects', 'action' => 'edit_pms', $pID));
    }

//end of function remove_pm

    public function make_pm($pID, $memberID) {
        $loggedINUserID = $this->Session->read('Auth.User.MemberID');
        $this->loadModel('ProjectManager');

        $this->ProjectManager->create();
        $this->ProjectManager->set('MemberID', $memberID);
        $this->ProjectManager->set('ProjectID', $pID);
        $this->ProjectManager->save();
        $loggedInUserName = $this->GetMemberName($loggedINUserID);
        $EditUserName = $this->GetMemberName($memberID);
        $this->loadModel('ProjectFeed');
        $comment = $loggedInUserName . " has promoted " . $EditUserName . " to project manager on the project " . $this->GetProjectName($pID);
        $this->ProjectFeed->create();
        $this->ProjectFeed->set('Date', date("y-m-d h:i:s"));
        $this->ProjectFeed->set('FeedVersion', 2);
        $this->ProjectFeed->set('ProjectID', $pID);
        $this->ProjectFeed->set('InitiatorID', $loggedINUserID);
        $this->ProjectFeed->set('type', 'make_pm');
        $this->ProjectFeed->set('ResourceID', $pID);
        $this->ProjectFeed->set('ResourceID2', $memberID);
        $this->ProjectFeed->set('Title', $comment);
        $this->ProjectFeed->save();

        $this->Session->setFlash("" . $EditUserName . " has been promoted to project manager.", 'success_message');
        $this->redirect(array('controller' => 'projects', 'action' => 'edit_pms', $pID));
    }

//end of fucntion make_pm

    public function remove_from_project($pID, $memberID) {

        $loggedINUserID = $this->Session->read('Auth.User.MemberID');
        $this->loadModel('ProjectManager');
        $this->loadModel('ProjectMember');
        $this->loadModel('ProjectFeed');
        $loggedInUserName = $this->GetMemberName($loggedINUserID);
        $EditUserName = $this->GetMemberName($memberID);

        $this->ProjectManager->query("DELETE FROM ProjectManagers WHERE MemberID = '" . $memberID . "' AND ProjectID = '" . $pID . "' LIMIT 1");
        $this->ProjectMember->query("DELETE FROM ProjectMembers WHERE MemberID = '" . $memberID . "' AND ProjectID = '" . $pID . "' LIMIT 1");

        $comment = $loggedInUserName . " has removed " . $EditUserName . " from the project " . $this->GetProjectName($pID);
        $this->ProjectFeed->create();
        $this->ProjectFeed->set('Date', date("y-m-d h:i:s"));
        $this->ProjectFeed->set('FeedVersion', 2);
        $this->ProjectFeed->set('ProjectID', $pID);
        $this->ProjectFeed->set('InitiatorID', $loggedINUserID);
        $this->ProjectFeed->set('type', 'remove_from_project');
        $this->ProjectFeed->set('ResourceID', $pID);
        $this->ProjectFeed->set('ResourceID2', $memberID);
        $this->ProjectFeed->set('Title', $comment);
        $this->ProjectFeed->save();
        $this->Session->setFlash("" . $EditUserName . " has been removed from the project.", 'success_message');
        $this->redirect(array('controller' => 'projects', 'action' => 'edit_pms', $pID));
    }

// end of function remove member from project

    public function ArchiveProject($ProjectID) {

        $loggedINUserID = $this->Session->read('Auth.User.MemberID');
        $loggedInUserName = $this->GetMemberName($loggedINUserID);
        $this->Project->create();
        $this->Project->set('ChangedDate', date('y-m-d h:i:s'));
        $this->Project->set('Archived', '1');
        $this->Project->set('ProjectID', $ProjectID);
        $this->Project->save();

        $this->loadModel('ProjectFeed');
        $comment = $loggedInUserName . " has archived the project " . $this->GetProjectName($ProjectID);
        $this->ProjectFeed->create();
        $this->ProjectFeed->create();
        $this->ProjectFeed->set('Date', date("y-m-d h:i:s"));
        $this->ProjectFeed->set('FeedVersion', 2);
        $this->ProjectFeed->set('ProjectID', $ProjectID);
        $this->ProjectFeed->set('InitiatorID', $loggedINUserID);
        $this->ProjectFeed->set('type', 'archive_project');
        $this->ProjectFeed->set('ResourceID', $ProjectID);
        $this->ProjectFeed->set('Title', $comment);
        $this->ProjectFeed->save();
        $this->Session->setFlash("The project has been archived.", 'success_message');
        $this->redirect(array('controller' => 'dashboard'));
    }

    public function new_contact() {
        
    }

}
