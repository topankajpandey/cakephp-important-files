<?php

ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class ContractsController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler', 'Common', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');
    private $proposals, $departments, $vendors, $projects;

    public function beforeFilter() {
        parent::beforeFilter();
        $this->proposals = $this->Common->get_contract_meta_data('Proposal', 'list', ['Proposal.id', 'Proposal.title']);
        $this->departments = $this->Common->get_contract_meta_data('Department', 'list', ['Department.id', 'Department.department']);
        $this->vendors = $this->Common->get_contract_meta_data('Vendor', 'list', ['Vendor.id', 'Vendor.vendor_name']);
        $this->projects = $this->Custom->get_projects('list', "", ['ProjectID', 'Name']);
    }

    public function index() {
        $this->set('refine_project_sidebar', true);
        $userDetail = $this->Session->read('Auth.User');
        $userID = $userDetail['MemberID'];
        if ($this->request->is('Post') && !empty($this->request->data['filter_project_id'])) {
            $this->set('set_project', $this->data['filter_project_id']);
            $keyword = trim($this->request->data['filter_project_id']);
            //$contractLists = $this->Contract->find('all', array('conditions' => array("OR" => array("Contract.title LIKE" => "%$keyword%", "Contract.description LIKE" => "%$keyword%"))));
            $contractLists = $this->Contract->find('all', array('conditions' => array('Contract.project_id' => $keyword)));
        } else {
            $contractLists = $this->Contract->find('all', array('conditions' => array('Contract.member_id' => $userID), 'order' => array('Contract.created' => 'DESC')));
        }
        $this->set('contractLists', $contractLists);
    }

    private function update_due_date($contract_id, $session = false) {
        if (!empty($this->data['end_date']) && !empty($this->data['date_key'])) {
            $this->loadModel('ContractMeta');
            
            $due_date_arr['date_key'] = $this->request->data['date_key'];
            $due_date_arr['start_dete'] = $this->request->data['start_date'];
            $due_date_arr['end_date'] = $this->request->data['end_date'];
            
            $due_date_arr = serialize($due_date_arr);
            $contractMeta = $this->ContractMeta->find('first', array('conditions' => array('ContractMeta.contract_id' => $contract_id, 'ContractMeta.contract_key' => 'conctract_due_dates')));
            if (empty($contractMeta)) {
                $this->ContractMeta->create();
                $this->ContractMeta->set('contract_id', $contract_id);
                $this->ContractMeta->set('contract_key', 'conctract_due_dates');
                $this->ContractMeta->set('contract_value', $due_date_arr);
            } else {
                $id = $contractMeta['ContractMeta']['id'];
                $this->ContractMeta->set('id', $id);
                $this->ContractMeta->set('contract_id', $contract_id);
                $this->ContractMeta->set('contract_key', 'conctract_due_dates');
                $this->ContractMeta->set('contract_value', $due_date_arr);
            }
            $this->ContractMeta->save();
            if ($session) {
                $this->Session->setFlash("Contract updated successfully...", 'success_message');
            }
        }
    }

    public function show_detail($id = null) {
        $this->loadModel('ContractMeta');
        $contract_id = base64_decode($id);
       
        if ($this->request->is('post')) {
            $this->update_due_date($contract_id, TRUE);
        }

        $this->data = $this->Contract->query("select cnt.*, dept.department, project.Name, vendors.vendor_name, proposals.title from contracts as cnt left join Projects as project on cnt.project_id = project.ProjectID left join departments as dept on cnt.department_id = dept.id left join vendors on cnt.id =  vendors.id left join proposals on cnt.proposal_id = proposals.id where cnt.id = '$contract_id'");
        $contractMeta = $this->ContractMeta->find('first', array('conditions' => array('ContractMeta.contract_id' => $contract_id, 'ContractMeta.contract_key' => 'conctract_due_dates')));
        $this->set('contractMeta', $contractMeta);
    }

    private function new_project($name, $description) {
        $memberId = $this->Session->read('Auth.User.MemberID');
        $memberName = $this->Session->read('Auth.User.FirstName') . ' ' . $this->Session->read('Auth.User.LastName');
        $NewProjectID = "";
        $this->loadModel('Project');
        $this->Project->create();
        $this->Project->set('Name', $name);
        $this->Project->set('Description', $description);
        $this->Project->set('CreatedDate', date('Y-m-d h:i:s'));
        $this->Project->set('ChangedDate', date('Y-m-d h:i:s'));

        if ($this->Project->save()) {
            $NewProjectID = $this->Project->getLastInsertId();
            //creating project manageer
            $this->loadModel('ProjectManager');
            $this->ProjectManager->create();
            $this->ProjectManager->set('AddedDate', date('Y-m-d h:i:s'));
            $this->ProjectManager->set('ProjectID', $NewProjectID);
            $this->ProjectManager->set('MemberID', $memberId);
            $this->ProjectManager->save();

            //creating settings for user
            $this->loadModel('SettingsUser');
            $this->SettingsUser->create();
            $this->SettingsUser->set('ProjectID', $NewProjectID);
            $this->SettingsUser->set('MemberID', $memberId);
            $this->SettingsUser->save();

            //creating settings for project
            $this->loadModel('SettingsProject');
            $this->SettingsProject->create();
            $this->SettingsProject->set('ProjectID', $NewProjectID);
            $this->SettingsProject->set('MemberID', $memberId);
            $this->SettingsProject->save();

            // creating project feed
            $this->loadModel('ProjectFeed');
            $this->ProjectFeed->create();
            $this->ProjectFeed->set('ProjectID', $NewProjectID);
            $this->ProjectFeed->set('ResourceID', $NewProjectID);
            $this->ProjectFeed->set('Date', date('Y-m-d h:i:s'));
            $this->ProjectFeed->set('FeedVersion', '2');
            $this->ProjectFeed->set('InitiatorID', $memberId);
            $this->ProjectFeed->set('type', 'new_contract_project');
            $this->ProjectFeed->set('Title', $memberName . ' has created the contract project for ' . $name);
            $this->ProjectFeed->save();
        }
        return $NewProjectID;
    }

    private function new_folder($folder_name, $project_id) {
        //Saving Folder Table  
        $this->loadModel('Folder');
        $this->Folder->create();
        $this->Folder->set('Name', $folder_name);
        $this->Folder->set('ProjectID', $project_id);
        $this->Folder->set('CreatedDate', date('Y-m-d H:i:s'));
        $this->Folder->set('ChangedDate', date('Y-m-d H:i:s'));
        $this->Folder->save();
    }

    public function add() {
        $memberId = $this->Session->read('Auth.User.MemberID');
        if (!empty($this->data)) {
            if ($this->Contract->validates()) {
                if ($this->Contract->save($this->request->data)) {
                    /* creating new project and its related folder */
                    $project_title = $this->request->data['Contract']['title'];
                    $description = $this->request->data['Contract']['description'];
                    $project_id = $this->new_project($project_title, $description);
                    /* creating new folder for related project */
                    if ($project_id) {
                        $this->new_folder(Configure::read('LEARNING_GUIDANCE'), $project_id);
                        $this->new_folder(Configure::read('GENERAL_CONTRACTING'), $project_id);
                        $this->new_folder(Configure::read('LEGAL'), $project_id);
                        $this->new_folder(Configure::read('STRATEGIC'), $project_id);
                        $this->new_folder(Configure::read('TOOLKITS'), $project_id);
                    }
                    $this->Session->setFlash("Contract added successfully...", 'success_message');
                    $this->redirect(array('controller' => 'contracts', 'action' => 'index'));
                }
            }
        }
        $this->set('proposals', $this->proposals);
        $this->set('departments', $this->departments);
        $this->set('vendors', $this->vendors);
        $this->set('projects', $this->projects);
        $this->set('member_id', $memberId);
    }
    
    private function get_contract_meta_by_key($contract_id,$key, $variable){
        $contractMeta = $this->ContractMeta->find('first', array('conditions' => array('ContractMeta.contract_id' => $contract_id, 'ContractMeta.contract_key' => $key)));
        $this->set($variable, $contractMeta);
    }
    
    private function update_custom_field($contract_id) {
        if (!empty($this->data['contract_key']) && !empty($this->data['contract_value'])) {
            $this->loadModel('ContractMeta');
            
            $custom_field_arr['contract_key'] = $this->request->data['contract_key'];
            $custom_field_arr['contract_value'] = $this->request->data['contract_value'];
            $custom_field_arr_serialize = serialize($custom_field_arr);
            $contractMeta = $this->ContractMeta->find('first', array('conditions' => array('ContractMeta.contract_id' => $contract_id, 'ContractMeta.contract_key' => 'contract_custom_field')));
            if (empty($contractMeta)) {
                $this->ContractMeta->create();
                $this->ContractMeta->set('contract_id', $contract_id);
                $this->ContractMeta->set('contract_key', 'contract_custom_field');
                $this->ContractMeta->set('contract_value', $custom_field_arr_serialize);
            } else {
                $id = $contractMeta['ContractMeta']['id'];
                $this->ContractMeta->set('id', $id);
                $this->ContractMeta->set('contract_id', $contract_id);
                $this->ContractMeta->set('contract_key', 'contract_custom_field');
                $this->ContractMeta->set('contract_value', $custom_field_arr_serialize);
            }
            $this->ContractMeta->save();
        }
    }

    public function edit($id = null) {
        $this->loadModel('ContractMeta');
        $contract_id = base64_decode($id);
        if (!empty($this->data)) {
            if ($this->Contract->validates()) {
                if ($this->Contract->save($this->request->data)) {
                    $this->update_custom_field($contract_id);
                    $this->update_due_date($contract_id, TRUE);
                    $this->Session->setFlash("Contract updated successfully...", 'success_message');
                    $this->redirect(array('controller' => 'contracts', 'action' => 'index'));
                }
            }
        }
        
        $this->get_contract_meta_by_key($contract_id,'contract_custom_field', 'contract_custom_field');
        $this->get_contract_meta_by_key($contract_id,'conctract_due_dates', 'contractMeta');
        
        $this->data = $this->Contract->find('first', array('conditions' => array('Contract.id' => $contract_id)));
        $this->set('proposals', $this->proposals);
        $this->set('departments', $this->departments);
        $this->set('vendors', $this->vendors);
        $this->set('projects', $this->projects);


        $contractMeta = $this->ContractMeta->find('first', array('conditions' => array('ContractMeta.contract_id' => $contract_id, 'ContractMeta.contract_key' => 'conctract_due_dates')));
        $this->set('contractMeta', $contractMeta);
    }

    function get_thread_by_project($project_id) {

        $forumData = $this->Forum->find('first', [
            'recursive' => -1,
            'conditions' => ['Forum.type' => 'contracts', 'Forum.ProjectID' => $project_id],
            'order' => ['Forum.PostedDate' => 'ASC'],
            'limit' => 1
                ]
        );
        if (!empty($forumData)) {
            return $forumData['Forum']['Thread'];
        } else {
            return false;
        }
    }

    public function view($id = null) {
        $id = base64_decode($id);
        $this->loadModel('Forum');
        $forum_thread = $this->get_thread_by_project($id);
        $forumData = $this->Forum->find('all', ['contain' => [
                'User' => [
                    'fields' => ['User.username', 'User.FirstName', 'User.LastName'],
                ],
            ],
            'conditions' => ['Forum.type' => 'contracts', 'Forum.Thread' => $forum_thread],
            'order' => ['Forum.PostedDate' => 'ASC']
                ]
        );
        $this->set('forumData', $forumData);
        $this->set('contract_id', $id);
    }

    public function delete($id = null) {

        $this->loadModel('ContractMeta');
        $contract_id = base64_decode($id);
        $this->autoRender = false;
        $this->Contract->id = $contract_id;
        if (!$this->Contract->exists()) {
            throw new NotFoundException(__('Invalid contracts'));
        }
        if ($this->Contract->delete($contract_id, true)) {
            $this->ContractMeta->query("delete from contract_meta where contract_id = '$contract_id'");
            $path = 'contractfiles/contract_' . $contract_id;
            $this->Common->remove_dir($path); // remove files directory
            $this->Session->setFlash("Contract deleted successfully...", 'success_message');
            $this->redirect(array('controller' => 'contracts', 'action' => 'index'));
        }
        $this->Session->setFlash("Contract are not deleted. Please try again", 'success_message');
        $this->redirect(array('controller' => 'contracts', 'action' => 'index'));
    }

    private function getNextThread() {
        $this->loadModel('Forum');
        $forumData = $this->Forum->find('first', array(
            'conditions' => array('Forum.type' => 'contracts'),
            'fields' => array('MAX(Forum.Thread) AS MaxThread')
                )
        );
        return ($forumData[0]['MaxThread'] + 1);
    }

    public function add_comment($id = null) {
        $this->loadModel('Forum');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $contract_id = base64_decode($id);
        if (!empty($this->data)) {
            if ($this->Forum->validates()) {
                $contract_id = $this->request->data['contract_id'];
                $memberId = $this->Session->read('Auth.User.MemberID');
                $forumPostData = $this->request->data['Forum'];
                $forumPostData['PostedBy'] = $memberId;
                $forumPostData['ProjectID'] = $contract_id;
                $forumPostData['type'] = 'contracts';
                $forumPostData['Thread'] = $this->getNextThread();

                if ($this->Forum->save($forumPostData)) {

                    $forum_id = $this->Forum->getLastInsertId();
                    $this->loadModel('ForumInvite');
                    if (!empty($this->request->data['forum_invite'])) {
                        $forum_invite = $this->request->data['forum_invite'];
                        $forumData = [];
                        foreach ($forum_invite as $key => $MemberID) {

                            $userData = $this->User->find('first', array('recursive' => 0, 'conditions' => array('User.MemberID' => $MemberID), 'fields' => array('User.Email')));
                            $email = $userData['User']['Email'];
                            $this->ForumInvite->query("INSERT INTO ForumInvite set ForumID = '$forum_id', Email = '$email', PostedDate = NOW()");
                            $emailHtml = $this->Custom->html_header();
                            $emailHtml .= '<tr><td class = "container-padding content" align = "left" style = "padding-left:24px;padding-right:24px;padding-top:12px;padding-bottom:12px;background-color:#ffffff"><br>
                                        <strong>You are invited for the new forum as below.</strong><br>
                                        <h5>Subject: ' . $forumPostData['Subject'] . '</h5>
                                        <h5>Message: ' . $forumPostData['Message'] . '</h5>
                                        <h5>Click to go to the forum with below link</h5>
                                        <h6><a href="' . Router::url("/", true) . 'forum">' . Router::url("/", true) . 'forum</a></h6>
                                        </td></tr >';
                            $emailHtml .= $this->Custom->html_footer();
                            mail($email, $forumPostData['Message'], $emailHtml, $this->Custom->email_header());
                        }
                    }

                    // creating project feed for the comment section
                    $this->loadModel('ProjectFeed');
                    $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                    $comment = $member_name . " has posted new contract forum";
                    $this->ProjectFeed->create();
                    $this->ProjectFeed->set('Date', date('Y-m-d H:i:s'));
                    $this->ProjectFeed->set('FeedVersion', 2);
                    $this->ProjectFeed->set('ProjectID', $forumPostData['ProjectID']);
                    $this->ProjectFeed->set('InitiatorID', $memberId);
                    $this->ProjectFeed->set('type', 'new_contract_post');
                    $this->ProjectFeed->set('ResourceID', $forum_id);
                    $this->ProjectFeed->set('ResourceID2', $this->getNextThread());
                    $this->ProjectFeed->set('NewValue', $forumPostData['Message']);
                    $this->ProjectFeed->set('Title', $comment);
                    $this->ProjectFeed->save();
                    $this->Session->setFlash("Comment added successfully...", 'success_message');
                    $this->redirect(array('controller' => 'contracts', 'action' => 'view', $id));
                }
            } else {
                $this->Session->setFlash("Please fill the required fields", 'success_message');
            }
        }

        $condition = ['User.Active' => 1, 'User.Access_Level' => 4, 'User.MemberID !=' => $memberId];
        $field = ['User.MemberID', 'FirstName', 'LastName'];
        $members = $this->Custom->get_members('all', $condition, $field, 0);
        $this->set('projectMember', $members);
        $this->set('contract_id', $contract_id);
    }

    private function reply_comment_process($data) {
        $this->loadModel('User');
        $this->loadModel('Forum');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $forumPostData = $this->request->data['Forum'];
        $forumPostData['type'] = 'contracts';
        $forum_invite = $this->request->data['forum_invite'];
        $forumPostData['PostedBy'] = $memberId;
        if ($this->Forum->save($forumPostData)) {

            $forum_id = $this->Forum->getLastInsertId();
            $this->loadModel('ForumInvite');
            if (!empty($forum_invite)) {
                $forumData = [];
                foreach ($forum_invite as $key => $MemberID) {
                    $userData = $this->User->find('first', array('recursive' => 0, 'conditions' => array('User.MemberID' => $MemberID), 'fields' => array('User.Email')));
                    $email = $userData['User']['Email'];
                    $this->ForumInvite->query("INSERT INTO ForumInvite set ForumID = '$forum_id', Email = '$email', PostedDate = NOW()");
                    $emailHtml = $this->Custom->html_header();
                    $emailHtml .= '<tr><td class = "container-padding content" align = "left" style = "padding-left:24px;padding-right:24px;padding-top:12px;padding-bottom:12px;background-color:#ffffff"><br>
                                        <strong>You are invited for the new forum as below.</strong><br>
                                        <h5>Subject: ' . $forumPostData['Subject'] . '</h5>
                                        <h5>Message: ' . $forumPostData['Message'] . '</h5>
                                        <h5>Click to go to the forum with below link</h5>
                                        <h6><a href="' . Router::url("/", true) . 'forum">' . Router::url("/", true) . 'forum</a></h6>
                                        </td></tr >';
                    $emailHtml .= $this->Custom->html_footer();
                    mail($email, $forumPostData['Message'], $emailHtml, $this->Custom->email_header());
                }
            }

            // saving project feed for contract post reply
            $this->loadModel('ProjectFeed');
            $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
            $comment = $member_name . " replied on the contract forum";
            $this->ProjectFeed->create();
            $this->ProjectFeed->set('Date', date('Y-m-d H:i:s'));
            $this->ProjectFeed->set('FeedVersion', 2);
            $this->ProjectFeed->set('ProjectID', $forumPostData['ProjectID']);
            $this->ProjectFeed->set('InitiatorID', $memberId);
            $this->ProjectFeed->set('type', 'new_contract_post_reply');
            $this->ProjectFeed->set('ResourceID', $forum_id);
            $this->ProjectFeed->set('ResourceID2', $forumPostData['Thread']);
            $this->ProjectFeed->set('NewValue', $forumPostData['Message']);
            $this->ProjectFeed->set('Title', $comment);
            $this->ProjectFeed->save();
        }
    }

    public function reply_comment($id = null) {
        $this->loadModel('Forum');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $contract_id = base64_decode($id);
        if (!empty($this->data)) {
            if ($this->Forum->validates()) {
                $project_id = base64_encode($this->request->data['Forum']['ProjectID']);
                $this->reply_comment_process($this->request->data);
                $this->Session->setFlash('Comment submitted successfully...', 'success_message');
                $this->redirect(array('controller' => 'contracts', 'action' => 'view', $project_id));
            }
        }

        $this->Forum->recursive = 0;
        $this->data = $this->Forum->find('first', ['contain' => [
                'User' => [
                    'fields' => ['User.MemberID', 'User.FirstName', 'User.LastName'],
                ],
            ],
            'conditions' => ['Forum.ForumPostID' => $contract_id]
                ]
        );
        $this->set('forumData', $this->data);

        $condition = ['User.Active' => 1, 'User.Access_Level' => 4, 'User.MemberID !=' => $memberId];
        $field = ['User.MemberID', 'FirstName', 'LastName'];
        $members = $this->Custom->get_members('all', $condition, $field, 0);
        $this->set('projectMember', $members);
        $this->set('contract_id', $contract_id);
    }

    function get_contract_files($contract_id) {
        $this->loadModel('ContractMeta');
        $contractLists = $this->ContractMeta->find('all', array('conditions' => array('ContractMeta.contract_id' => $contract_id, 'ContractMeta.contract_key' => 'contract_files'), 'order' => array('ContractMeta.id' => 'DESC')));
        $contract_file_arr = [];
        $html = '<div id="tabs-contract-files" class="forum-table">';
        $html .= '<table><thead id="tblHead"></thead>';
        $html .= '<tbody>';
        if (!empty($contractLists)) {
            foreach ($contractLists as $data) {
                $fileData = unserialize($data['ContractMeta']['contract_value']);
                $html .= '<tr>';
                $ext = explode('.', $fileData['file_name']);
                if ($ext[1] == 'jpg' || $ext[1] == 'jpeg' || $ext[1] == 'PNG' || $ext[1] == 'png' || $ext[1] == 'GIF' || $ext[1] == 'gif') {
                    $file_img = '<img src = "' . $fileData['url'] . '" alt = "' . $fileData['file_name'] . '"  width="30" height="30">';
                } else {
                    $file_img = '<img src="' . $this->webroot . 'img/file_screen.png" alt="dummy_file" width="30" height="30">';
                }
                $html .= '<td>' . $file_img . '</td>';
                $html .= '<td>' . $fileData['file_name'] . '</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td align="center" colspan="3">No files found..</td><td></td></tr>';
        }
        $html .= '<tbody>';
        $html .= '</table></div>';
        return $html;
    }

    function view_contract_files($contract_id = null) {
        $this->loadModel('ContractMeta');
        $get_files_data = $this->get_contract_files($contract_id);
        $this->Custom->send(200, $get_files_data);
    }

    private function create_contract_directory($contract_id) {
        if (!is_dir('contractfiles/contract_' . $contract_id)) {
            mkdir('contractfiles/contract_' . $contract_id, 0777, true);
            return 'contractfiles/contract_' . $contract_id . '/';
        }
        return 'contractfiles/contract_' . $contract_id . '/';
    }

    private function contract_file_rename($name, $auto_id) {
        $path_info = pathinfo($name);
        $ext = "." . $path_info["extension"];
        $filename = time() . $auto_id . $ext;
        return $filename;
    }

    function add_contract_files() {
        if ($this->request->is('Post')) {
            $this->loadModel('ContractMeta');
            $contract_id = $this->data['contract_base_id'];
            $fileArr = 'contract_files_' . $contract_id;
            if (!empty($_FILES[$fileArr]['name'][0])) {
                $path = $this->create_contract_directory($contract_id);
                $contractMeta = [];
                $dir_url = Router::url('/', true) . 'contractfiles/contract_' . $contract_id . '/';
                for ($i = 0; $i < count($_FILES[$fileArr]['name']); $i++) {
                    $filename = $this->contract_file_rename($_FILES[$fileArr]['name'][$i], $i);
                    move_uploaded_file($_FILES[$fileArr]['tmp_name'][$i], $path . $filename);
                    $contract_value = serialize(['file_name' => $filename, 'url' => $dir_url . $filename]);
                    $contractMeta[$i] = [
                        'ContractMeta' => [
                            'contract_id' => $contract_id,
                            'contract_key' => 'contract_files',
                            'contract_value' => $contract_value,
                        ]
                    ];
                }
                $this->ContractMeta->saveMany($contractMeta);
                $get_files_data = $this->get_contract_files($contract_id);
                $this->Custom->send(200, $get_files_data);
            } else {
                $this->Custom->send(401, 'Please select at least one file');
            }
        }
    }

}
