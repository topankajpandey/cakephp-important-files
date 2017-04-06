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
        }

        if ((isset($this->params['prefix']) && ($this->params['prefix'] == 'admin'))) {
            $this->layout = 'admin';
        }
    }

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
        $PID = $this->Custom->getExistMemberInProject($memberId);
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
        $membersLists = $this->Member->find('all', array('conditions' => array('Member.Active' => (int) 1), 'fields' => array('Member.MemberID', 'Member.username')));

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

    function GetContactList($MemberID) {
        $ContactIDs = array();
        $ContactIDs = $this->GetContacts($MemberID);
        $ContactList = array();
        if ($ContactIDs) {
            $ContactList = $this->User->find('all', array('conditions' => array('User.MemberID IN' => $ContactIDs), 'contain' => array(), 'fields' => array('User.MemberID', 'User.FirstName', 'User.LastName', 'User.Email')));
        }
        return $ContactList;
    }

    // Get New messages for user
    public function GetNewMessages($MemberID) {
        $this->loadModel('Message');
        $NewMessages = array();
        $getAllMessage = $this->Message->find('all', array('conditions' => array('Message.ToID' => $MemberID, 'Message.isRead' => (int) 0), 'fields' => array('Message.ToID', 'Message.FromID')));
        return $NewMessages;
    }

    public function ProfilePic($MemberID, $Size) {

        $picQuery = $this->User->find('first', array('conditions' => array('User.MemberID' => $MemberID), 'fields' => array('User.ProfilePic')));
        return $profilePic = $picQuery['User']['ProfilePic'];
        /* if ($profilePic == "") {
          switch ($Size) {
          case 1: return "0-avatar_150.png";
          break;
          case 2: return "0-avatar_39.png";
          break;
          case 3: return "0-avatar_150.png";
          break;
          case 4: return "0-avatar_75.png";
          break;
          }
          } else {
          list($width, $height) = getimagesize(Configure::read('SITEURL') . '/files/User/UserProfilePics/000000100.png');
          if ($height != 0)
          $AspectRatio = $width / $height;
          else
          $AspectRatio = 0;
          switch ($Size) {
          case 1: $html = "width='" . $width . "' height='" . $height . "'";
          break;
          case 2:
          if ($AspectRatio > 1.5)
          $html = "width='39'";   // pic is wide
          if ($AspectRatio <= 1.5)
          $html = "height='26'";   // pic is tall
          break;
          case 3:
          if ($AspectRatio > 1.5)
          $html = "width='150'";   // pic is wide
          if ($AspectRatio <= 1.5)
          $html = "height='100'";   // pic is tall
          break;
          case 4:
          if ($AspectRatio > 1.5)
          $html = "width='75'";   // pic is wide
          if ($AspectRatio <= 1.5)
          $html = "height='50'";   // pic is tall
          break;
          }
          return '1-' . $html . '-' . $profilePic;
          } */
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

    public function GetMemberName($MemberID) {
        $userData = $this->User->find('first', array('conditions' => array('User.MemberID' => $MemberID), 'fields' => array('User.FirstName', 'User.LastName')));
        return $userData['User']['FirstName'] . " " . $userData['User']['LastName'];
    }

    public function GetProjectName($ProjectID) {
        $projectData = $this->Project->find('first', array('conditions' => array('Project.ProjectID' => $ProjectID), 'fields' => array('Project.Name')));
        if ($ProjectID == 0)
            return "None";
        else
            return $projectData['Project']['Name'];
    }

    public function isContact($ContactID) {
        $MemberID = $this->Session->read('Auth.User.MemberID');
        $this->loadModel('Contact');
        $Contact = "no";
        $contactList = $this->Contact->query("SELECT count(*) as totalcount FROM Contacts WHERE (Contacts.MemberID1 = '" . $ContactID . "' AND Contacts.MemberID2 = '" . $MemberID . "') OR (Contacts.MemberID1 = '" . $MemberID . "' AND Contacts.MemberID2 = '" . $ContactID . "')");

        $contactCount = $contactList[0][0]['totalcount'];
        if ($contactCount > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getProjectTaskList($order = false, $order_by = false, $id = false) {
        $condition = [];
        if ($id) {
            $condition = ['Task.ProjectID' => $id];
        }
        //$taskLists = $this->Task->find('all', array('recursive' => 3, 'conditions' => $condition, 'fields' => array('Task.ProjectID', 'Task.Name', 'Task.AssignedBy', 'Task.AssignedTo', 'Task.StartDate', 'Task.EndDate'), 'order' => array($order => $order_by)));
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
    
    

}
