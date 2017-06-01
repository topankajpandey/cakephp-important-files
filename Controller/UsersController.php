<?php

ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
CONST LIMIT = 10;

class UsersController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');

    public function beforeFilter() {

        parent::beforeFilter();
        $this->Auth->allow(array(
            'login',
            'signup',
            'forgot',
            'reset',
            'verification',
            'admin_login',
            'change_user_picture'
                )
        );
    }

    public function admin_dashboard() {
        $MemberID = $this->Session->read('Auth.User.MemberID');
        $this->loadModel('Project');
        $this->loadModel('Contract');
        $users = $this->User->find('all', array('recursive' => false, 'conditions' => array('User.username !=' => 'admin')));
        $this->set("user_count", count($users));

        $projects = $this->Project->find('all', array('recursive' => false, 'conditions' => array('Project.Deleted' => 0, 'Project.Archived' => 0), 'order' => 'Project.CreatedDate DESC', 'limit' => 6));
        $this->set("projects", $projects);

        $contracts = $this->Contract->find('count');
        $this->set("contract_count", $contracts);

        $tasks = $this->Task->find('all', array('conditions' => array('Task.Deleted' => 0), 'order' => 'Task.CreatedDate DESC', 'limit' => 6));
        $this->set("tasks", $tasks);
    }

    public function admin_profile() {
        $MemberID = $this->Session->read('Auth.User.MemberID');
        if (!empty($this->request->data)) {
            $this->User->save($this->request->data);
            $this->Session->setFlash('Profile updated successfully...');
            $this->redirect(array('controller' => 'users', 'action' => 'profile'));
        } else {
            $this->data = $this->User->find('first', array('conditions' => array('User.MemberID' => $MemberID)));
        }
        $countryList = $this->GetCountryList();
        $this->set('countryList', $countryList);
    }

    public function admin_change_password() {
        $this->loadModel('User');
        if (!empty($this->request->data)) {
            $this->User->save($this->request->data['User']);
            $this->Session->setFlash('Password updated successfully...');
            $this->redirect(array('controller' => 'users', 'action' => 'profile'));
        }
    }

    public function admin_parmission($id = null) {
        $this->loadModel('Permission');
        $this->loadModel('User');
        if (!empty($this->request->data)) {

            $check_count = $this->Permission->find('count', array('conditions' => array('Permission.user_id' => $this->request->data['user_id'])));
            $data['Permission']['actions'] = implode(",", $this->request->data['permission']);
            $data['Permission']['user_id'] = $user_id = $this->request->data['user_id'];

            if ($check_count > 0) {
                $action = "'" . $data['Permission']['actions'] . "'";
                $this->Permission->updateAll(array('Permission.actions' => $action), array('Permission.user_id' => $user_id));
                $this->Session->setFlash('Permission updated successfully...');
                $this->redirect(array('controller' => 'users', 'action' => 'parmission', $user_id));
            } else {
                $this->Permission->save($data);
                $this->Session->setFlash('Permission saved successfully...');
                $this->redirect(array('controller' => 'users', 'action' => 'parmission', $user_id));
            }
        }
        $getPermission = $this->Permission->find('first', array('fields' => array('Permission.actions'), 'conditions' => array('Permission.user_id' => $id)));
        $this->set('getPermission', $getPermission);
        $getUser = $this->User->find('first', array('fields' => array('User.MemberID', 'User.FirstName', 'User.LastName'), 'conditions' => array('User.MemberID' => $id)));
        $this->set('getUser', $getUser);
    }

    public function admin_login() {
        $this->layout = 'admin/login';
        $this->set('page_title', 'Login');
        if ($this->Session->read('Auth.User')) {
            if (!empty($this->Session->read('Auth.User.MemberID'))) {
                if ($this->Session->read('Auth.User.Access_Level') == '1') {
                    $this->redirect(array('action' => 'dashboard', 'admin' => true));
                } else if ($this->Session->read('Auth.User.Access_Level') == '4') {
                    $this->redirect(array('action' => 'dashboard', 'admin' => false));
                } else {
                    $this->redirect(array('action' => 'dashboard', 'admin' => false));
                }
            }
        }
        if ($this->request->is('Post')) {
            App::Import('Utility', 'Validation');
            if (isset($this->data['User']['email']) && Validation::email($this->data['User']['email'])) {
                $this->request->data['User']['email'] = $this->data['User']['email'];
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
                        'userModel' => 'User',
                        'username' => 'email'
                    )
                );
                $x = $this->User->find('first', array('conditions' => array('email' => $this->data['User']['email'], 'Active' => (int) 1, 'verified' => (int) 1)));
            } else {
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
                        'userModel' => 'User',
                        'username' => 'email'
                    )
                );
                $x = $this->User->find('first', array('conditions' => array('username' => $this->data['User']['email'], 'Active' => (int) 1, 'verified' => (int) 1)));
            }
            if (!empty($x) && ($this->Session->read('Auth.User.Access_Level') == '1')) {
                if (($x['User']['Access_Level'] == '1' || $x['User']['Access_Level'] == '2') && $x['User']['Active'] == '1') {
                    if (!$this->Auth->login()) {
                        $this->Session->setFlash('Invalid email or password.', 'error');
                    } else {
                        $this->Session->write('user_session_data', $x['Permission']);
                        $this->redirect(array('controller' => 'users', 'action' => 'admin_dashboard'));
                    }
                } else {
                    $this->Session->setFlash("Not authorities or account is inactive", 'error');
                }
            } else if (!empty($x)) {
                if (($x['User']['Access_Level'] == '1' || $x['User']['Access_Level'] == '2') && $x['User']['Active'] == '1') {
                    if ($this->Session->read('Auth.User.Access_Level') == '4') {
                        $this->redirect(array('action' => 'dashboard', 'admin' => false));
                    } else {
                        if (!$this->Auth->login()) {
                            $this->Session->setFlash('Invalid email or password.', 'error');
                        } else {
                            $this->Session->write('user_session_data', $x['Permission']);
                            $this->redirect(array('controller' => 'users', 'action' => 'admin_dashboard'));
                        }
                    }
                } else {
                    $this->Session->setFlash("Not authorities or account is inactive", 'error');
                }
            } else {
                $this->Session->setFlash("user does not exist.", 'error');
            }
        }
    }

    public function admin_logout() {
        $this->Auth->logout();
        $this->redirect('/admin');
    }

    public function admin_list_admin() {

        $this->get_authorize('users/list_admin');
        $this->loadModel('User');
        $this->User->recursive = 0;
        if ($this->request->is('post')) {
            $keyword = trim($this->request->data['query']);
            if (!empty($keyword)) {
                @$records = $this->User->find('all', array('conditions' => array("OR" => array("User.email LIKE" => "%$keyword%", "User.FirstName LIKE" => "%$keyword%", "User.LastName LIKE" => "%$keyword%", "User.PhoneNumber LIKE" => "%$keyword%"), 'User.Access_Level' => '1', 'User.Access_Level' => '2')));
            }
            $this->set("admin", @$records, $this->paginate());
            if (count(@$records) == 0) {
                $this->Session->setFlash("No Record found");
            }
        } else {
            $this->paginate = array('conditions' => array('OR' => array('User.Access_Level' => '1', 'User.Access_Level' => '2')), 'order' => array('User.MemberID' => 'ASC'), 'limit' => 10);
            $this->set('admin', $this->paginate());
        }
    }

    public function admin_add_admin() {
        $this->get_authorize('users/add_admin');
        $this->loadModel('User');
        if ($this->request->is('post')) {
            $this->User->set($this->data);
            if ($this->User->validates()) {
                $userdata = $this->request->data['User'];
                $userPicture = $this->request->data['Picture'];
                $userdata['Access_Level'] = 2;
                $userdata['JoinedDate'] = date('Y-m-d');

                if ($_FILES['profile_pic']['name'] != "") {
                    $one['name'] = str_replace(' ', '+', $_FILES['profile_pic']['name']);
                    $userdata['ProfilePic'] = $userdata['PhoneNumber'] . '' . $one['name'];
                } else {
                    $userdata['ProfilePic'] = '';
                }

                if ($this->User->save($userdata)) {
                    if ($_FILES['profile_pic']['error'] == 0) {
                        $pth = 'files' . DS . 'admin' . DS . $userdata['PhoneNumber'] . '' . $one['name'];
                        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $pth);
                    }
                    $this->Session->setFlash(__('Admin created successfully...'));
                    $this->redirect(array('action' => 'list_admin'));
                } else {
                    $this->Session->setFlash(__('Please try again later.'));
                }
            }
        }
    }

    public function admin_edit_admin($id = null) {
        $this->get_authorize('users/edit_admin');
        $this->loadModel('User');
        if ($this->request->is('post') || $this->request->is('put')) {

            if ($this->User->validates()) {
                $userdata = $this->request->data['User'];
                if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] == 0) {
                    $old = 'files/member/' . $userdata['ProfilePic'];
                    unlink($old);
                    $one['name'] = str_replace(' ', '+', $_FILES['profile_pic']['name']);
                    $pth = 'files' . DS . 'admin' . DS . $userdata['PhoneNumber'] . '' . $one['name'];
                    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $pth);
                    $userdata['ProfilePic'] = $userdata['PhoneNumber'] . '' . $one['name'];
                } else {
                    $userdata['ProfilePic'] = $userdata['ProfilePic'];
                }
                if ($this->User->save($userdata)) {
                    $this->Session->setFlash(__('Admin updated successfully...'));
                    $this->redirect(array('action' => 'list_admin'));
                }
            }
        } else {
            $this->data = $this->User->find('first', array('conditions' => array('User.MemberID' => $id)));
        }
    }

    public function admin_delete_admin($id = null) {
        $this->get_authorize('users/delete_admin');
        $this->autoRender = false;
        $this->loadModel('User');
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->delete($id, true)) {
            $this->Session->setFlash(__('Admin deleted successfully...'));
            $this->redirect(array('action' => 'list_admin'));
        }
        $this->Session->setFlash(__('Admin are not deleted. Please try again.'));
        $this->redirect(array('action' => 'list_admin'));
    }

    public function admin_activate_admin($id = null) {
        $this->get_authorize('users/activate_admin');
        $this->autoRender = false;
        $this->loadModel('User');
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->updateAll(
                        array('User.Active' => '0'), array('User.MemberID' => $id)
                )) {
            $this->Session->setFlash(__('Admin activated successfully...'));
            $this->redirect(array('action' => 'list_admin'));
        }
        $this->Session->setFlash(__('Please try again later.'));
        $this->redirect(array('action' => 'list_admin'));
    }

    public function admin_deactivate_admin($id = null) {
        $this->get_authorize('users/deactivate_admin');
        $this->autoRender = false;
        $this->loadModel('User');
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->updateAll(
                        array('User.Active' => '1'), array('User.MemberID' => $id)
                )) {
            $this->Session->setFlash(__('Admin deactivated successfully...'));
            $this->redirect(array('action' => 'list_admin'));
        }
        $this->Session->setFlash(__('Please try again later.'));
        $this->redirect(array('action' => 'list_admin'));
    }

    public function admin_list_member() {
        $this->get_authorize('users/list_member');
        $this->get_authorize('users/list_member');
        $this->loadModel('User');
        $this->loadModel('Membership');
        $this->User->recursive = 0;
        $membership = $this->Membership->find('all');
        if ($this->request->is('post')) {
            $keyword = trim($this->request->data['query']);
            if (!empty($keyword)) {
                @$records = $this->User->find('all', array('conditions' => array("OR" => array("User.email LIKE" => "%$keyword%", "User.FirstName LIKE" => "%$keyword%", "User.LastName LIKE" => "%$keyword%", "User.PhoneNumber LIKE" => "%$keyword%"), 'User.Access_Level' => '4')));
            }
            $this->set("member", @$records, $this->paginate());
            if (count(@$records) == 0) {
                $this->Session->setFlash("No Record found");
            }
        } else {
            $this->paginate = array('conditions' => array('User.username !=' => 'admin', 'User.Access_Level' => '4'), 'order' => array('User.MemberID' => 'ASC'), 'limit' => 10);
            $this->set('member', $this->paginate());
            $this->set('membership', $membership);
        }
    }

    private function email_header() {
        $Email_headers = "From: info@projectengineer.net\r\n" .
                "Cc: info@projectengineer.net\r\n" .
                "Reply-To: info@projectengineer.net\r\n" .
                "Return-Path: info@projectengineer.net\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/html; charset=ISO-8859-1\r\n";
        return $Email_headers;
    }

    public function admin_add_member() {
        $this->get_authorize('users/add_member');
        $this->loadModel('User');
        if ($this->request->is('post')) {
            $this->User->set($this->data);

            if ($this->User->validates()) {

                $userdata = $this->request->data['User'];
                $userPicture = $this->request->data['Picture'];
                $userdata['Access_Level'] = 4;
                $userdata['JoinedDate'] = date('Y-m-d');

                if ($_FILES['profile_pic']['name'] != "") {
                    $one['name'] = str_replace(' ', '+', $_FILES['profile_pic']['name']);
                    $userdata['ProfilePic'] = $userdata['PhoneNumber'] . '' . $one['name'];
                } else {
                    $userdata['ProfilePic'] = '';
                }

                if ($this->User->save($userdata)) {
                    if ($_FILES['profile_pic']['error'] == 0) {
                        $pth = 'files' . DS . 'member' . DS . $userdata['PhoneNumber'] . '' . $one['name'];
                        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $pth);
                    }
                    $this->Session->setFlash(__('Member created successfully...'));
                    $this->redirect(array('action' => 'list_member'));
                } else {
                    $this->Session->setFlash(__('Please try again later.'));
                }
            }
        }
    }

    public function admin_edit_member($id = null) {
        $this->get_authorize('users/edit_member');
        $this->loadModel('User');
        if ($this->request->is('post') || $this->request->is('put')) {

            if ($this->User->validates()) {
                $userdata = $this->request->data['User'];
                if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] == 0) {
                    $old = 'files/member/' . $userdata['ProfilePic'];
                    unlink($old);
                    $one['name'] = str_replace(' ', '+', $_FILES['profile_pic']['name']);
                    $pth = 'files' . DS . 'member' . DS . $userdata['PhoneNumber'] . '' . $one['name'];
                    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $pth);
                    $userdata['ProfilePic'] = $userdata['PhoneNumber'] . '' . $one['name'];
                } else {
                    $userdata['ProfilePic'] = $userdata['ProfilePic'];
                }
                if ($this->User->save($userdata)) {
                    $this->Session->setFlash(__('Member updated successfully...'));
                    $this->redirect(array('action' => 'list_member'));
                }
            }
        } else {
            $this->data = $this->User->find('first', array('conditions' => array('User.MemberID' => $id)));
        }
    }

    public function admin_delete_member($id = null) {
        $this->get_authorize('users/delete_member');
        $this->autoRender = false;
        $this->loadModel('User');
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->delete($id, true)) {
            $this->Session->setFlash(__('Member deleted successfully...'));
            $this->redirect(array('action' => 'list_member'));
        }
        $this->Session->setFlash(__('Member are not deleted. Please try again.'));
        $this->redirect(array('action' => 'list_member'));
    }

    public function admin_activate_member($id = null) {
        $this->get_authorize('users/activate_member');
        $this->autoRender = false;
        $this->loadModel('User');
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->updateAll(
                        array('User.Active' => '0'), array('User.MemberID' => $id)
                )) {
            $this->Session->setFlash(__('Account activated successfully...'));
            $this->redirect(array('action' => 'list_member'));
        }
        $this->Session->setFlash(__('Please try again later.'));
        $this->redirect(array('action' => 'list_member'));
    }

    public function admin_deactivate_member($id = null) {
        $this->get_authorize('users/deactivate_member');
        $this->autoRender = false;
        $this->loadModel('User');
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->updateAll(
                        array('User.Active' => '1'), array('User.MemberID' => $id)
                )) {
            $this->Session->setFlash(__('Account deactivated successfully...'));
            $this->redirect(array('action' => 'list_member'));
        }
        $this->Session->setFlash(__('Please try again later.'));
        $this->redirect(array('action' => 'list_member'));
    }

    public function login() { //start of func login//
        $this->layout = 'login';
        $this->set('page_title', 'Login');
        if ($this->Session->read('Auth.User')) {
            if (!empty($this->Session->read('Auth.User.MemberID'))) {
                if ($this->Session->read('Auth.User.Access_Level') == '1') {
                    $this->redirect(array('action' => 'dashboard', 'admin' => true));
                } else if ($this->Session->read('Auth.User.Access_Level') == '4') {
                    $this->redirect(array('action' => 'dashboard'));
                } else {
                    $this->redirect(array('action' => 'dashboard'));
                }
            }
        }
        if ($this->request->is('Post')) {
            App::Import('Utility', 'Validation');
            if (isset($this->data['User']['email']) && Validation::email($this->data['User']['email'])) {
                $this->request->data['User']['email'] = $this->data['User']['email'];
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
                        'userModel' => 'User',
                        'username' => 'email'
                    )
                );
                $x = $this->User->find('first', array('conditions' => array('email' => $this->data['User']['email'], 'Active' => (int) 1, 'verified' => (int) 1)));
            } else {
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
                        'userModel' => 'User',
                        'username' => 'email'
                    )
                );
                $x = $this->User->find('first', array('conditions' => array('username' => $this->data['User']['email'], 'Active' => (int) 1, 'verified' => (int) 1)));
            }

            if (!empty($x)) {
                if ($this->Session->read('Auth.User.Access_Level') == '1') {
                    $this->redirect(array('action' => 'dashboard', 'admin' => true));
                } else if ($this->Session->read('Auth.User.Access_Level') == '4') {
                    $this->redirect(array('action' => 'dashboard'));
                } else if ($x['User']['username'] == 'admin') {
                    if ($this->Auth->login()) {
                        $this->Session->write('user_session_data', $x['Permission']);
                        $this->redirect(array('action' => 'dashboard', 'admin' => true));
                    }
                } else {
                    if (!$this->Auth->login()) {
                        $this->Session->setFlash('Please check your password.', 'error');
                        $this->redirect(array('controller' => '/'));
                    } else {

                        $this->Session->setFlash('Successfully signed in', 'success_message');
                        return $this->redirect($this->Auth->redirectUrl());
                    }
                }
            } else {// if not user exist
                $this->Session->setFlash("Invalid username / password or your account is not activated yet.", 'error');
                $this->redirect(array('controller' => '/'));
            }//end of if user exist
        }
    }

    /*
     * Contact invite through email
     * By Pankaj
     * Date: 07.07.2017 
     */

    public function invite_contact() {

        if ($this->request->is('ajax')) {
            $status = 201;
            $html = "";
            $this->loadModel('Contact');
            $this->loadModel('ProjectMember');
            $memberId = $this->Session->read('Auth.User.MemberID');
            $contactArr = $this->request->data['Contact'];
            $checkEmailExist = $this->User->find('count', array('conditions' => array('User.Email' => $contactArr['InviteeEmail'])));
            if ($checkEmailExist == 0) {
                $checkContactExist = $this->Contact->find('count', array('conditions' => array('Contact.InviteeEmail' => $contactArr['InviteeEmail'], 'Contact.MemberID1' => $memberId)));
                if ($checkContactExist == 0) {
                    $contactArr['MemberID1'] = $memberId;
                    $contactArr['MemberID2'] = 0;
                    $contactArr['Confirmed'] = 0;
                    $contactArr['Date'] = date('Y-m-d H:i:s');
                    if ($this->Contact->save($contactArr)) {
                        $contact_id = $this->Contact->getLastInsertId();

                        /*                         * ***************** Saving project member for the invitation ************************ */
                        if (!empty($this->request->data['ProjectID'])) {
                            foreach ($this->request->data['ProjectID'] as $ProjectID) {
                                $checkProjectMemberExist = $this->ProjectMember->find('count', array('conditions' => array('ProjectMember.ProjectID' => $ProjectID, 'ProjectMember.ContactID' => $contact_id)));
                                if ($checkProjectMemberExist == 0) {
                                    $projectMemberArr['ProjectMember']['AddedDate'] = date('Y-m-d H:i:s');
                                    $projectMemberArr['ProjectMember']['ProjectID'] = $ProjectID;
                                    $projectMemberArr['ProjectMember']['MemberID'] = $memberId;
                                    $projectMemberArr['ProjectMember']['ContactID'] = $contact_id;
                                    $this->ProjectMember->save($projectMemberArr);
                                }
                            }
                        }

                        /*                         * ***************** Send Email to User************************ */
                        $InviterName = ucfirst($this->Session->read('Auth.User.FirstName')) . ' ' . ucfirst($this->Session->read('Auth.User.LastName'));
                        $email_message = '<tr><td class = "container-padding content" align = "left" style = "padding-left:24px;padding-right:24px;padding-top:12px;padding-bottom:12px;background-color:#ffffff"><br>
                                <h5>Dear ' . ucfirst($contactArr['FirstName']) . '<h5>
                                <h6>You have been invited by ' . $InviterName . ' to join ProjectEngineer, a web based project management system for engineers.</h6>
                                <br>
                                <h5>Click to go to the forum with below link</h5>
                                <h6><a href="' . Router::url("/", true) . 'forum">' . Router::url("/", true) . 'forum</a></h6>
                                </td>
                            </tr >';

                        $Email = new CakeEmail();
                        $Email->emailFormat('html');
                        $Email->template('default');
                        $Email->to($contactArr['InviteeEmail']);
                        $Email->subject('Find the invitation');
                        $Email->from('webmaster@projectengineer.net');
                        $Email->send($email_message);
                        /*                         * ***************************************** */
                        $status = 200;
                        $html = '<p style="color:green;">Invitation sent successfully...</p>';
                    }
                } else {
                    $html = '<p style="color:red;">Contact already exist...</p>';
                }
            } else {
                $html = '<p style="color:red;">Email already exist...</p>';
            }

            $this->Custom->send($status, $html);
        }
    }

    public function signup() {//start of function signup
        $this->layout = 'login';
        $this->set('page_title', 'Registration');

        if ($this->request->is('Post')) {
            //debug($this->data); exit;
            $this->request->data['User']['JoinedDate'] = date('y-m-d h:i:s');
            $this->request->data['User']['SubscriptionType'] = 'Free';
            $this->request->data['User']['Access_Level'] = 4;
            $this->User->set($this->request->data);
            if ($this->User->save($this->request->data)) {

                $lastId = base64_encode('aaqw' . $this->User->getLastInsertId() . 'aaqw');
                $verificationLink = 'http://dev414.trigma.us/projectengineer/users/verification/' . $lastId;
                //Email verification code
                $Email = new CakeEmail();
                $Email->from(array('info@projectengineer.net' => 'Project Engineer'));
                $Email->to($this->data['User']['email']);
                $Email->subject('Account Verification Email :: Project Engineer');
                $Email->send('Please click on this link to activate your account: ' . $verificationLink);
                //End of email verification code
                $this->Session->setFlash("You has been successfully registered in our system, please check your email for to activate your account", 'success');
                $this->redirect(array('controller' => '/'));
            } else {
                $this->Session->setFlash('Please correct below errors', 'error');
            }
        }
    }

//end of function signup

    public function verification($id) {//end of function verification
        $id = str_replace('aaqw', '', base64_decode($id));
        $this->User->create();
        $this->User->set('verified', (int) 1);
        $this->User->set('Active', (int) 1);
        $this->User->set('MemberID', $id);
        if ($this->User->save()) {
            $this->Session->setFlash("Your account has been successfully verified", 'success');
            $this->redirect(array('controller' => '/'));
        } else {
            $this->Session->setFlash("We are facing some issue..please try again after some time", 'error');
            $this->redirect(array('controller' => '/'));
        }
    }

    public function get_count() {
        $this->loadModel('ProjectFeed');
        $count = $this->ProjectFeed->find('count');
        return $count;
    }

    public function forgot() {
        $this->layout = 'login';
        $this->set('page_title', 'Forgot Password');
    }

    public function dashboard() {
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->loadModel('Document');
        $this->loadModel('Forum');
        if ($this->request->is('ajax')) {
            $offset = $this->request->data['row'];
            $projectData = $this->Custom->get_project_feeds('all', NULL, 1, LIMIT, $offset);
            $this->set('projectData', $projectData);
            $this->render('ajax-data');
        }
        $this->layout = 'login';
        $projectData = $this->Custom->get_project_feeds('all', NULL, 1, LIMIT, 0);
        $this->set('projectData', $projectData);

        $taskData = $this->getProjectTaskList('Task.EndDate', 'Desc');
        $this->set('taskData', $taskData);

        $forumLists = $this->Forum->find('all', array('recursive' => -1, 'conditions' => array('Forum.type' => 'projects', 'Forum.PostedBy' => $memberId, 'Forum.Archived' => (int) 0, 'Forum.Level' => (int) 0), 'order' => array('Forum.PostedDate' => 'DESC'), 'limit' => 6));
        $this->set('forumLists', $forumLists);

        $this->set('projectList', $this->get_project_by_login_user('all', ['Documents'], NULL, 3));

        $this->set('feedCount', $this->get_count());
    }

    public function logout() {
        $this->Auth->logout();
        $this->Session->delete('Userdefined');
        $this->Session->setFlash("Successfully logged out.", 'success');
        return $this->redirect($this->Auth->logout());
    }

    public function change_user_picture() {
        $memberId = $this->Session->read('Auth.User.MemberID');
        $userdata = [];
        if ($this->request->is('ajax')) {
            if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] == 0) {
                $getUser = $this->User->find('first', array('fields' => array('User.ProfilePic'), 'conditions' => array('User.MemberID' => $memberId)));
                $old = 'files/member/' . $getUser['User']['ProfilePic'];
                if (file_exists($old)) {
                    unlink($old);
                }
                $one['name'] = str_replace(' ', '+', $_FILES['profile_pic']['name']);
                $pth = 'files' . DS . 'member' . DS . time() . '' . $one['name'];
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], $pth);
                $userdata['User']['ProfilePic'] = time() . '' . $one['name'];

                $userdata['User']['MemberID'] = $memberId;
                $this->Session->write('Auth.User.ProfilePic', $userdata['User']['ProfilePic']);
                if ($this->User->save($userdata)) {
                    $srcfile = $this->webroot . 'files/member/' . $userdata['User']['ProfilePic'];
                    echo json_encode(array('src' => $srcfile, 'res' => 1));
                } else {
                    echo json_encode(array('src' => '', 'res' => 0));
                }
            } else {
                $userData = $this->Session->read('Auth.User');
                $srcfile = $this->webroot . 'files/member/' . $userData['ProfilePic'];
                echo json_encode(array('src' => $srcfile, 'res' => 2));
            }
        }
        $this->autoRender = false;
    }

    public function remove_photo() {
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->User->create();
        $this->User->set('MemberID', $memberId);
        $this->User->set('ProfilePic', '');
        $this->User->set('ChangedDate', date("y-m-d"));
        if ($this->User->save()) {
            $this->Session->setFlash("Your photo has been removed", 'success_message');
            $this->redirect(array('controller' => 'dashboard'));
        } else {
            $this->Session->setFlash("something went wrong, please try again after some time", 'error_message');
            $this->redirect(array('controller' => 'dashboard'));
        }
    }

//end of function RemovePhoto

    public function edit_profile() {
        $memberId = $this->Session->read('Auth.User.MemberID');
        $countryList = $this->GetCountryList();
        $this->set('countryList', $countryList);
        $stateList = $this->GetStateList();
        $this->set('stateList', $stateList);
        $provinceList = $this->GetProvinceList();
        $this->set('provinceList', $provinceList);

        if (!empty($this->data)) {

            $this->request->data['User']['ChangedDate'] = date('y-m-d h:i:s');
            $this->request->data['User']['MemberID'] = $memberId;
            $this->User->set($this->request->data);
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash("Your profile has been updated successfully", 'success_message');
                $this->redirect(array('controller' => 'edit_profile'));
            } else {
                $this->Session->setFlash("We are facing some issue....please try again after some time", 'error_message');
                $this->redirect(array('controller' => 'edit_profile'));
            }
        } else {
            $this->data = $this->User->find('first', array('conditions' => array('User.MemberID' => $memberId)));
        }
    }

    public function change_password() {

        if (!empty($this->data)) {
            $this->User->set($this->request->data);
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash('Password has been changed successfully.', 'success_message');
                $this->redirect(array('controller' => 'edit_profile'));
                // call $this->redirect() here
            } else {
                $this->Session->setFlash('Password could not be changed.', 'error_message');
            }
        }
    }

    public function GetCountryList() {
        $CountryList = array("Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, The Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote D'ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and McDonald Islands", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran, Islamic Republic of", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, Former Yuslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and The Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia/South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City State", "Venezuela", "Viet Nam", "Virgin Islands (British)", "Virgin Islands (US)", "Wallis and Futuna", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");

        return $CountryList;
    }

    function GetCountryID($CountryName) {
        // for beanstream. Must match beanstream's list.
        $CountryID = array("AL", "DZ", "AS", "AD", "AO", "AI", "AQ", "AG", "AR", "AM", "AW", "AU", "AT", "AZ", "BS", "BH", "BD", "BB", "BY", "BE", "BZ", "BJ", "BM", "BT", "BO", "BA", "BW", "BV", "BR", "IO", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "KY", "CF", "TD", "CL", "CN", "CX", "CC", "CO", "KM", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "TP", "EC", "EG", "SV", "GQ", "ER", "EE", "ET", "FK", "FO", "FJ", "FI", "FR", "GF", "PF", "TF", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GD", "GP", "GU", "GT", "GN", "GW", "GY", "HT", "HM", "HN", "HK", "HU", "IS", "IN", "ID", "IR", "IQ", "IE", "IL", "IT", "JM", "JP", "JO", "KZ", "KE", "KI", "KP", "KR", "KW", "KG", "LA", "LV", "LB", "LS", "LR", "LY", "LI", "LT", "LU", "MO", "MK", "MG", "MW", "MY", "MV", "ML", "MT", "MH", "MQ", "MR", "MU", "YT", "MX", "FM", "MD", "MC", "MN", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "AN", "NC", "NZ", "NI", "NE", "NG", "NU", "NF", "MP", "NO", "OM", "PK", "PW", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RE", "RO", "RU", "RW", "KN", "LC", "VC", "WS", "SM", "ST", "SA", "SN", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "GS", "ES", "LK", "SH", "PM", "SD", "SR", "SJ", "SZ", "SE", "CH", "SY", "TW", "TJ", "TZ", "TH", "TG", "TK", "TO", "TT", "TN", "TR", "TM", "TC", "TV", "UG", "UA", "AE", "GB", "US", "UM", "UY", "UZ", "VU", "VA", "VE", "VN", "VG", "VI", "WF", "EH", "YE", "YU", "ZM", "ZW");

        $CountryNames = GetCountryList();

        $x = 0;
        while ($CountryName != $CountryNames[$x])
            $x++;
        return $CountryID[$x];
    }

    public function GetProvinceID($ProvinceName) {
        // for beanstream.  Must match beanstream's list.
        $ProvinceID = array("AB", "BC", "MB", "NB", "NS", "NT", "NU", "ON", "PE", "QC", "SK", "YT", "NF");

        $ProvinceNames = GetProvinceList();
        $x = 0;
        while ($ProvinceName != $ProvinceNames[$x])
            $x++;
        return $ProvinceID[$x];
    }

    public function GetStateID($StateName) {
        // for beanstream.
        $StateID = array("AL", "AR", "AS", "AZ", "CA", "CO", "CT", "DC", "DE", "FL", "FM", "GA", "GU", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MD", "ME", "MI", "MN", "MO", "MP", "MS", "MT", "NC", "ND", "NE", "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "PR", "RI", "SC", "SD", "TN", "TX", "UT", "VA", "VI", "VT", "WA", "WI", "WV", "WY");

        $StateNames = GetStateList();
        $x = 0;
        while ($StateName != $StateNames[$x])
            $x++;
        $x--;
        return $StateID[$x];
    }

    public function GetProvinceList() {
        $ProvinceList = array("Alberta", "British Columbia", "Manitoba", "New Brunswick", "Newfoundland", "Nova Scotia", "Northwest Territories", "Nunavut", "Ontario", "Prince Edward Island", "Quebec", "Saskatchewan", "Yukon");
        return $ProvinceList;
    }

    public function GetStateList() {
        $StateList = array("Alaska", "Alabama", "Arkansas", "American Samoa", "Arizona", "California", "Colorado", "Connecticut", "District of Columbia", "Delaware", "Florida", "Micronesia", "Georgia", "Guam", "Hawaii", "Iowa", "Idaho", "Illinois", "Indiana", "Kansas", "Kentucky", "Louisiana", "Massachusetts", "Maryland", "Maine", "Michigan", "Minnesota", "Missouri", "Northern Marianas", "Mississippi", "Montana", "North Carolina", "North Dakota", "Nebraska", "New Hampshire", "New Jersey", "New Mexico", "Nevada", "New York", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Puerto Rico", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Virginia", "Virgin Islands", "Vermont", "Washington", "Wisconsin", "West Virginia", "Wyoming");

        return $StateList;
    }

    public function contact_request() {
        $this->loadModel('Contact');
        $MemberID = $this->Session->read('Auth.User.MemberID');
        $contactData = $this->Contact->query("SELECT Contacts.ContactID, Contacts.MemberID2, Contacts.MemberID2, Members.FirstName, Members.LastName, Members.email, Members.ProfilePic FROM Contacts LEFT JOIN Members ON Contacts.MemberID1 = Members.MemberID WHERE Contacts.MemberID2 =  '$MemberID' AND Contacts.Confirmed =0");

        $this->set('contactData', $contactData);
        $this->viewPath = 'Elements/contract';
        $this->render('contact_request');
    }

    public function accept_request($contact_id) {
        if ($this->request->is('ajax')) {
            $this->loadModel('Contact');
            $this->Contact->query("UPDATE Contacts set Confirmed = 1 where ContactID = '$contact_id'");
            $this->Custom->send(200, "Request accepted successfully");
        }
    }

    public function get_users_listing($search_keyword, $project = false) {
        $MemberID = $this->Session->read('Auth.User.MemberID');
        $keyword = explode(" ", strtolower($search_keyword));
        $userData = $this->User->find('all', array('contain' => array(), 'conditions' => array('AND' => array('User.MemberID !=' => $MemberID, 'User.Access_Level' => Configure::read('MEMBER_USER')), 'OR' => array('User.FirstName IN' => $keyword, 'User.LastName IN' => $keyword, 'User.email IN' => $keyword, 'User.username IN' => $keyword, 'User.FirstName like' => $search_keyword . "%", 'User.LastName like' => $search_keyword . "%", 'User.email like' => $search_keyword . "%", 'User.username like' => $search_keyword . "%")), 'fields' => array('User.MemberID', 'User.FirstName', 'User.LastName', 'User.email', 'User.username', 'User.ProfilePic', 'User.Company', 'User.City', 'User.Province', 'User.Country', 'User.Address1')));
        $this->set('userData', $userData);
        if ($project) {
            $this->set('projectid', $project);
        }
        $this->viewPath = 'Elements/contract';
        $this->render('contract_search');
    }

    public function add_contact($ContactID, $projectids = false) {
        $MemberID = $this->Session->read('Auth.User.MemberID');
        $get_member = $this->GetMemberName($ContactID, 'other_detail');
       
        if ($projectids) {
            $this->loadModel('ProjectMember');
            $mem['ProjectMember']['ProjectID'] = $projectids;
            $mem['ProjectMember']['MemberID'] = $ContactID;
            $mem['ProjectMember']['AddedDate'] = date('Y-m-d h:i:s');
            $this->ProjectMember->create();
            $this->ProjectMember->save($mem);
        } else {
            $this->loadModel('Contact');
            $this->Contact->create();
            $this->Contact->set('Date', date('Y-m-d h:i:s'));
            $this->Contact->set('MemberID1', $MemberID);
            $this->Contact->set('MemberID2', $ContactID);
            $this->Contact->set('InviteeEmail', $get_member['email']);
            $this->Contact->set('FirstName', $get_member['first_name']);
            $this->Contact->set('LastName', $get_member['last_name']);
            $this->Contact->set('Confirmed', '0');
            $this->Contact->save();
            $name = $this->GetMemberName($MemberID);
            $Subject = Configure::read('EMAILPREFIX') . " You have a new contact request";
        }
        echo '1';
        exit();
    }

    public function admin_add_user_membership() {
        $this->autoRender = false;
        $this->loadModel('Subscription');
        $membershipdata['Subscription']['user_id'] = $_POST['user_id'];
        $membershipdata['Subscription']['membership_id'] = $_POST['member_id'];
        $membershipdata['Subscription']['created'] = date('Y-m-d h:i:s');
        $membershipdata['Subscription']['status'] = 1;
        $this->Subscription->create();
        if ($this->Subscription->save($membershipdata)) {
            $lastid = $this->Subscription->getLastInsertId();
            $this->Subscription->updateAll(
                    array('Subscription.status' => "'0'"), array('Subscription.user_id' => $_POST['user_id'], 'Subscription.id !=' => $lastid)
            );
            echo "success";
        } else {
            echo "error";
        }
    }

}

?>