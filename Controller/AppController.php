<?php

App::uses('Controller', 'Controller');

class AppController extends Controller {

    public $components = array('Session', 'Auth', 'RequestHandler', 'DebugKit.Toolbar', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('User', 'Project', 'ProjectMember', 'Contact', 'Member', 'Task');

    /*
      Function Name: beforeFilter
      Author Name: Trigma Solutions
      Date: 21 dec 2016
      Purpose: this is common function and acts as constructor
     */

    public function beforeFilter() {
        $this->layout = 'login';
        if ($this->Session->check('Auth.User.MemberID')) {
            $memberId = $this->Session->read('Auth.User.MemberID');
            $this->set('profile_pic', $this->ProfilePic($memberId, 3));
            $this->User->contain();
            $userData = $this->User->find('first', array('conditions' => array('User.MemberID' => $memberId)));
            $this->set('loggedInUserData', $userData);
            $this->getProjectList($memberId);
            $this->getMemberList($memberId);
            $this->set('GetContactList', $this->GetContactList($memberId));
            $this->set('AnyUnconfirmedContacts', $this->AnyUnconfirmedContacts($memberId));

            $projects = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
            $this->set('login_user_projects', $this->project_dropdown($projects));
        }

        if ((isset($this->params['prefix']) && ($this->params['prefix'] == 'admin'))) {
            $this->layout = 'admin';
        }
    }

    public function NextThread($type) {
        $this->loadModel('Forum');
        $forumData = $this->Forum->find('first', array(
            'conditions' => array('Forum.type' => $type),
            'fields' => array('MAX(Forum.Thread) AS MaxThread')
                )
        );
        return ($forumData[0]['MaxThread'] + 1);
    }

    public function create_project_feed($project_id, $type, $ResourceID, $ResourceID2, $NewValue, $comment) {
        $this->loadModel('ProjectFeed');
        $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
        $InitiatorID = $this->Session->read('Auth.User.MemberID');
        $comment_data = $member_name . ' ' . $comment;
        $projectFeed['Date'] = date('Y-m-d H:i:s');
        $projectFeed['FeedVersion'] = 2;
        $projectFeed['ProjectID'] = $project_id;
        $projectFeed['InitiatorID'] = $InitiatorID;
        $projectFeed['type'] = $type;
        $projectFeed['ResourceID'] = $ResourceID;
        $projectFeed['ResourceID2'] = $ResourceID2;
        $projectFeed['NewValue'] = $NewValue;
        $projectFeed['Title'] = $comment_data;
        $this->ProjectFeed->save($projectFeed);
    }

    public function project_dropdown($data) {
        $selected_project_id = "";
        if ($this->request->is('post') && !empty($this->data['filter_project_id'])) {
            $selected_project_id = $this->data['filter_project_id'];
        }
        $curent_action = $this->webroot . $this->params['controller'] . '/' . $this->request->params['action'];
        $html = '<form action="' . $curent_action . '" method="post" accept-charset="utf-8">';
        $html .= '<select name="filter_project_id" class="form-control" onchange="filtered_by_project()">';
        if (!empty($data)) {
            $html .= '<option value="">Sort By Project</option>';
            foreach ($data as $key => $value) {
                if ($selected_project_id == $key) {
                    $condition = 'selected="selected"';
                } else {
                    $condition = '';
                }
                $html .= '<option value="' . $key . '"' . $condition . '>' . $value . '</option>';
            }
        } else {
            $html .= '<option value="">No project available</option>';
        }
        $html .= '</select>';
        $html .= '</form>';
        return $html;
    }

    /*
      Purpose: get the array list of project id based on manager id
      Author Name: Trigma Solutions
      Date: 21 dec 2016
     */

    public function get_project_ID_by_manager_id() {
        $this->loadModel('ProjectManager');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $managerArr = $this->ProjectManager->find('all', [
            'conditions' => array('ProjectManager.MemberID' => $memberId),
                ]
        );
        $projects = array();
        if (!empty($managerArr)) {
            foreach ($managerArr as $manager) {
                $projects[] = $manager['ProjectManager']['ProjectID'];
            }
        }
        return $projects;
    }

    /*
      Purpose: get projetcs type based on defined params
      Author Name: Trigma Solutions
      Date: 21 dec 2016
     */

    public function get_project_by_ids($type = false, $recursive = -1, $ProjectID = NULL) {
        $this->loadModel('ProjectManager');
        $project_ids = $this->get_project_ID_by_manager_id();
        if (!empty($project_ids)) {
            $conditions = ['Project.ProjectID IN' => $project_ids, 'Project.Archived' => 0, 'Project.Deleted' => 0];
            if ($ProjectID) {
                $condition = ['Project.ProjectID' => $ProjectID, 'Project.Deleted' => 0, 'Project.Archived' => 0];
            }
            $projectArr = $this->Project->find($type, [
                'recursive' => $recursive,
                'conditions' => $conditions,
                    ]
            );
            return $projectArr;
        }
        return count($project_ids);
    }

    /*
      Purpose: get projetcs based on login user
      Author Name: Trigma Solutions
      Date: 21 dec 2016
     */

    public function get_project_by_login_user($type = false, $contain = [], $ProjectID = NULL, $limit = false) {
        $this->loadModel('ProjectManager');
        $project_ids = $this->get_project_ID_by_manager_id();
        $projectArr = [];
        if (!empty($project_ids)) {
            $conditions = ['Project.ProjectID IN' => $project_ids, 'Project.Archived' => 0, 'Project.Deleted' => 0];
            if ($ProjectID) {
                $condition = ['Project.ProjectID' => $ProjectID, 'Project.Deleted' => 0, 'Project.Archived' => 0];
            }
            $projectArr = $this->Project->find($type, [
                'contain' => $contain,
                'conditions' => $conditions,
                'limit' => $limit,
                    ]
            );
        }
        return $projectArr;
    }

    /*
      Purpose: Return the length of authority according to their plan
      Author Name: Trigma Solutions
      Date: 21 dec 2016
     */

    public function userPlanMembership($check_rule = false) {
        $memberId = $this->Session->read('Auth.User.MemberID');
        $check_rule_length = 1;
        $userdata = $this->User->find('first', [
            'contain' => array(
                'Subscription' => array(
                    'Membership',
                    'conditions' => ['Subscription.status' => 1],
                    'fields' => ['user_id', 'membership_id']
                )
            ),
            'conditions' => array('User.MemberId' => $memberId),
                ]
        );
        if (!empty($userdata['Subscription'])) {
            if (!empty($userdata['Subscription']['Membership'])) {
                $check_rule_length = $userdata['Subscription']['Membership'][$check_rule];
            }
        }
        return $check_rule_length;
    }

    /*
      Purpose: Protect feature and functionality for unauthorize user role
      Author Name: Trigma Solutions
      Date: 21 dec 2016
     */

    protected function get_authorize($action_value) {
        $index = $this->Session->read('user_session_data');
        if (!empty($index)) {
            $memberId = $this->Session->read('Auth.User.MemberID');
            if ($memberId == 100)
                return true;
            $explodePermissionList = explode(",", $index['actions']);
            if (in_array($action_value, $explodePermissionList)) {
                return TRUE;
            } else {
                $this->Session->setFlash(__('<strong>Unauthorized! </strong> You dont have permission to access that page...'));
                $this->redirect(array('controller' => 'users', 'action' => 'dashboard', 'admin' => true));
            }
        }
        $this->Session->setFlash(__('<strong>Unauthorized! </strong> You dont have permission to access that page...'));
        $this->redirect(array('controller' => 'users', 'action' => 'dashboard', 'admin' => true));
    }

    /*
      Function Name: getProjectList
      Author Name: Trigma Solutions
      Date: 21 dec 2016
      Purpose: This function is used for fetching the projects list of loggedin user.
     */

    public function getProjectList($memberId) {

        $PID = $this->get_project_ID_by_manager_id();
        $projectLists = [];
        if (count($PID) > 0) {
            $projectLists = $this->Project->find('all', array('conditions' => array('Project.ProjectID IN' => $PID, 'Project.Archived' => (int) 0, 'Project.Deleted' => (int) 0), 'fields' => array('Project.ProjectID', 'Project.Name', 'Project.Description'), 'order' => array('Project.ProjectID' => 'desc')));
        }
        $this->set('projectLists', $projectLists);
    }

    /*
      Function Name: getProjectMembers
      Author Name: Trigma Solutions
      Date: 07 feb 2017
      Purpose: This fnction is used for fetching the projects members.
     */

    public function getMemberList($memberId) {
        $membersLists = array();
        $memberId = $this->Session->read('Auth.User.MemberID');
        $membersLists = $this->Member->find('all', array('conditions' => array('Member.Access_Level' => (int) 4, 'Member.Active' => (int) 1, 'Member.MemberID !=' => $memberId), 'fields' => array('Member.MemberID', 'Member.username')));
        $this->set('membersLists', $membersLists);
    }

    // Find out if there are any unconfirmed contacts
    public function AnyUnconfirmedContacts($MemberID) {
        $AnyUnconfirmedContacts = 0;
        $getAllContacts = $this->Contact->find('all', array('conditions' => array('Contact.MemberID2' => $MemberID)));
        foreach ($getAllContacts as $contact) {
            if ($contact['Contact']['Confirmed'] == (int) 0) {
                $AnyUnconfirmedContacts = 1;
            }
        }
        return $AnyUnconfirmedContacts;
    }

    // Read the contact list for the member
    public function GetContacts($MemberID) {
        $Contacts = array();
        $getAllContacts = $this->Contact->find('all', array('conditions' => array('OR' => array('Contact.MemberID2' => $MemberID, 'Contact.MemberID1' => $MemberID), 'Contact.Confirmed' => (int) 1)));

        foreach ($getAllContacts as $contact) {
            if ($contact['Contact']['MemberID1'] == $MemberID)
                $Contacts[] = $contact['Contact']['MemberID2'];
            if ($contact['Contact']['MemberID2'] == $MemberID)
                $Contacts[] = $contact['Contact']['MemberID1'];
        }
        return $Contacts;
    }

    // Get the contact list based on member id
    function GetContactList($MemberID) {
        $ContactIDs = array();
        $ContactIDs = $this->GetContacts($MemberID);
        $ContactList = array();
        if ($ContactIDs) {
            $ContactList = $this->User->find('all', array('conditions' => array('User.MemberID IN' => $ContactIDs), 'contain' => array(), 'fields' => array('User.MemberID', 'User.FirstName', 'User.LastName', 'User.Email')));
        }
        return $ContactList;
    }

    // Get New messages for user based on member id
    public function GetNewMessages($MemberID) {
        $this->loadModel('Message');
        $NewMessages = array();
        $getAllMessage = $this->Message->find('all', array('conditions' => array('Message.ToID' => $MemberID, 'Message.isRead' => (int) 0), 'fields' => array('Message.ToID', 'Message.FromID')));
        return $NewMessages;
    }

    // Get member name based on member id and aditional parameter
    public function GetMemberName($MemberID, $format = false) {
        $userData = $this->User->find('first', array('conditions' => array('User.MemberID' => $MemberID), 'fields' => array('User.FirstName', 'User.LastName', 'User.email')));
        if ($format) {
            return [
                'first_name' => $userData['User']['FirstName'],
                'last_name' => $userData['User']['LastName'],
                'email' => $userData['User']['email'],
            ];
        } else {
            return $userData['User']['FirstName'] . " " . $userData['User']['LastName'];
        }
    }

    // Get project name based on project id
    public function GetProjectName($ProjectID) {
        $projectData = $this->Project->find('first', array('conditions' => array('Project.ProjectID' => $ProjectID), 'fields' => array('Project.Name')));
        if ($ProjectID == 0)
            return "None";
        else
            return $projectData['Project']['Name'];
    }


    // check the contact list based on contact id
    public function isContact($ContactID, $project = false) {
        $MemberID = $this->Session->read('Auth.User.MemberID');
        $this->loadModel('Contact');
        $this->loadModel('ProjectMember');
        $Contact = "no";

        if ($project) {
            $condition = array('ProjectMember.MemberID' => $ContactID, 'ProjectMember.ProjectID' => $project);
            $manager1 = $this->ProjectMember->find('first', array('conditions' => $condition));
            if (!empty($manager1)) {
                return 1;
            } else {
                return 0;
            }
        } else {
            $contactList = $this->Contact->query("SELECT count(*) as totalcount FROM Contacts WHERE (Contacts.MemberID1 = '" . $ContactID . "' AND Contacts.MemberID2 = '" . $MemberID . "') OR (Contacts.MemberID1 = '" . $MemberID . "' AND Contacts.MemberID2 = '" . $ContactID . "')");
            $contactCount = $contactList[0][0]['totalcount'];
            if ($contactCount > 0) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    // Get the project task list based on defined params
    public function getProjectTaskList($order = false, $order_by = false, $id = false) {
        $this->loadModel('Task');
        $condition = [];
        if ($id) {
            $condition = ['Task.ProjectID' => $id];
        }

        $taskLists = $this->Task->find('all', ['contain' => [
                'Project' => [
                    'ProjectManager' => ['User'],
                    'fields' => ['Project.Name', 'Project.Description'],
                ],
            ],
            'conditions' => $condition,
            'fields' => ['Task.ProjectID', 'Task.Name', 'Task.AssignedBy', 'Task.AssignedTo', 'Task.StartDate', 'Task.EndDate'],
            'order' => [$order => $order_by]
                ]
        );
        return $taskLists;
    }

    /* I think the above is unneccessary function. Don't waste time on it */

    public function ProfilePic($MemberID, $Size) {
        $picQuery = $this->User->find('first', array('conditions' => array('User.MemberID' => $MemberID), 'fields' => array('User.ProfilePic')));
        return $profilePic = $picQuery['User']['ProfilePic'];
    }

    // Returns an encrypted number from a string encrypted by EncryptNum().
    public function GetEncryptNum($String) {

        // If the encryption string is a 0, it was a failed encryption, therefore automatically return failure at unencryption.
        if ($String != 0) {
            debug($String);
            exit;
            $Ref = 3;
            $Num2 = substr($String, $Ref, 1);
            // Test 1 is if the 4th character is a number
            if ($Num2 == "0" || $Num2 == "1" || $Num2 == "2" || $Num2 == "3" || $Num2 == "4" || $Num2 == "5" || $Num2 == "6" || $Num2 == "7" || $Num2 == "8" || $Num2 == "9")
                $Test1 = "Yes";
            else
                $Test1 = "No";

            $Char3 = substr($String, $Ref + 1, $Num2);
            $Ref += $Num2 + 1;

            $Num4 = substr($String, $Ref, 1);
            // Test 2 is if the character denoting the encryption length is a number
            if ($Num4 == "0" || $Num4 == "1" || $Num4 == "2" || $Num4 == "3" || $Num4 == "4" || $Num4 == "5" || $Num4 == "6" || $Num4 == "7" || $Num4 == "8" || $Num4 == "9")
                $Test2 = "Yes";
            else
                $Test2 = "No";

            $Char5 = substr($String, $Ref + 1, $Num4);
            for ($i = 0; $i < strlen($Char5); $i++) {
                if ($Char5[$i] == "f")
                    $Char5[$i] = "0";
                if ($Char5[$i] == "D")
                    $Char5[$i] = "1";
                if ($Char5[$i] == "n")
                    $Char5[$i] = "2";
                if ($Char5[$i] == "H")
                    $Char5[$i] = "3";
                if ($Char5[$i] == "x")
                    $Char5[$i] = "4";
                if ($Char5[$i] == "w")
                    $Char5[$i] = "5";
                if ($Char5[$i] == "K")
                    $Char5[$i] = "6";
                if ($Char5[$i] == "a")
                    $Char5[$i] = "7";
                if ($Char5[$i] == "T")
                    $Char5[$i] = "8";
                if ($Char5[$i] == "o")
                    $Char5[$i] = "9";
            }

            $Ref += strlen($Char5) + 1;
            $Char6 = substr($String, $Ref, 10);
            // Test 3 is if the pre-determined 10 character string is correct
            if ($Char6 == "uFwd7dSe3a")
                $Test3 = "Yes";
            else
                $Test3 = "No";

            $Ref += 10;
            $Num7 = substr($String, $Ref, 1);
            // Test 4 is if the character denoting the length of the last set of random numbers is a number
            if (is_numeric($Num7) && ($Num7 == "0" || $Num7 == "1" || $Num7 == "2" || $Num7 == "3" || $Num7 == "4" || $Num7 == "5" || $Num7 == "6" || $Num7 == "7" || $Num7 == "8" || $Num7 == "9"))
                $Test4 = "Yes";
            else
                $Test4 = "No";

            if ($Test1 == "Yes" && $Test2 == "Yes" && $Test3 == "Yes" && $Test4 == "Yes")
                return (int) $Char5;
            else
                return 0;
        } else
            return 0;
    }

    // Returns an encrypted number from a string encrypted by EncryptNum().
    // Same as GetEncryptNum.  Trying to change the name and make GetEncryptNum obsolete
    public function DecryptNum($String) {
        // If the encryption string is a 0, it was a failed encryption, therefore automatically return failure at unencryption.
        if ($String != "0") {
            $Ref = 3;
            $Num2 = substr($String, $Ref, 1);
            // Test 1 is if the 4th character is a number
            if ($Num2 == "0" || $Num2 == "1" || $Num2 == "2" || $Num2 == "3" || $Num2 == "4" || $Num2 == "5" || $Num2 == "6" || $Num2 == "7" || $Num2 == "8" || $Num2 == "9")
                $Test1 = "Yes";
            else
                $Test1 = "No";

            $Char3 = substr($String, $Ref + 1, $Num2);
            $Ref += $Num2 + 1;

            $Num4 = substr($String, $Ref, 1);
            // Test 2 is if the character denoting the encryption length is a number
            if ($Num4 == "0" || $Num4 == "1" || $Num4 == "2" || $Num4 == "3" || $Num4 == "4" || $Num4 == "5" || $Num4 == "6" || $Num4 == "7" || $Num4 == "8" || $Num4 == "9")
                $Test2 = "Yes";
            else
                $Test2 = "No";

            $Char5 = substr($String, $Ref + 1, $Num4);
            for ($i = 0; $i < strlen($Char5); $i++) {
                if ($Char5[$i] == "f")
                    $Char5[$i] = "0";
                if ($Char5[$i] == "D")
                    $Char5[$i] = "1";
                if ($Char5[$i] == "n")
                    $Char5[$i] = "2";
                if ($Char5[$i] == "H")
                    $Char5[$i] = "3";
                if ($Char5[$i] == "x")
                    $Char5[$i] = "4";
                if ($Char5[$i] == "w")
                    $Char5[$i] = "5";
                if ($Char5[$i] == "K")
                    $Char5[$i] = "6";
                if ($Char5[$i] == "a")
                    $Char5[$i] = "7";
                if ($Char5[$i] == "T")
                    $Char5[$i] = "8";
                if ($Char5[$i] == "o")
                    $Char5[$i] = "9";
            }

            $Ref += strlen($Char5) + 1;
            $Char6 = substr($String, $Ref, 10);
            // Test 3 is if the pre-determined 10 character string is correct
            if ($Char6 == "uFwd7dSe3a")
                $Test3 = "Yes";
            else
                $Test3 = "No";

            $Ref += 10;
            $Num7 = substr($String, $Ref, 1);
            // Test 4 is if the character denoting the length of the last set of random numbers is a number
            if (is_numeric($Num7) && ($Num7 == "0" || $Num7 == "1" || $Num7 == "2" || $Num7 == "3" || $Num7 == "4" || $Num7 == "5" || $Num7 == "6" || $Num7 == "7" || $Num7 == "8" || $Num7 == "9"))
                $Test4 = "Yes";
            else
                $Test4 = "No";

            if ($Test1 == "Yes" && $Test2 == "Yes" && $Test3 == "Yes" && $Test4 == "Yes")
                return (int) $Char5;
            else
                return 0;
        } else
            return 0;
    }

}
