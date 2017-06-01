<?php

ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
CONST STATUS_CODE = 200;
CONST VERSION = 1;

class DocumentsController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom', 'Common');
    public $helpers = array('Html', 'Form', 'Session', 'Custom');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('login', 'signup', 'forgot', 'reset', 'verification'));
    }

    public function index() {
        $this->set('refine_project_sidebar', true);
        $this->loadModel('Project');
        $this->loadModel('ProjectMember');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $PID = $this->get_project_ID_by_manager_id();
        $projects = [];
        if ($this->request->is('post') && !empty($this->data['filter_project_id'])) {
            $this->set('set_project', $this->data['filter_project_id']);
            $projects = $this->Project->find('all', array(
                'contain' => array(
                    'Folders' => array(
                        'conditions' => array(
                            array('Folders.Deleted' => 0)
                        ),
                        'Documents',
                        'Forum'
                    )
                ),
                'conditions' => array('Project.ProjectID IN' => $PID, 'Project.ProjectID' => $this->data['filter_project_id'])
                    )
            );
            //$this->Session->write('Userdefined.project_id', $this->request->data['project_id']);
        } else {

            if (!empty($PID)) {
                $session_project_id = $this->Session->read('Userdefined.project_id');
                $conditionsBefore = array('Project.ProjectID IN' => $PID, 'Project.Deleted' => 0, 'Project.Archived' => 0);
                $conditionAfter = $this->Custom->checkSessionProject($session_project_id, $conditionsBefore);

                $projects = $this->Project->find('all', array(
                    'contain' => array(
                        'Folders' => array(
                            'conditions' => array(
                                array('Folders.Deleted' => 0)
                            ),
                            'Documents',
                            'Forum'
                        )
                    ),
                    'conditions' => $conditionAfter
                        )
                );
            }
        }
        $this->set('projectData', $projects);
        $projectList = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectList', $projectList);
    }

    private function create_document_directory($project_id) {
        if (!is_dir('documentfiles/project_' . $project_id)) {
            mkdir('documentfiles/project_' . $project_id, 0777, true);
            return 'documentfiles/project_' . $project_id . '/';
        }
        return 'documentfiles/project_' . $project_id . '/';
    }

    private function document_file_name($name) {
        $path_info = pathinfo($name);
        $ext = "." . $path_info["extension"];
        $filename = time() . $ext;
        return $filename;
    }

    public function NewDocument() {
        if ($this->request->is('post')) {
            //Saving Folder Table
            $this->loadModel('Folder');
            $memberId = $this->Session->read('Auth.User.MemberID');
            $folderArr = $this->request->data['Folder'];
            $documentArr = $this->request->data['Document'];
            $folderArr['CreatedBy'] = $memberId;
            $filename = '';

            if (empty($folderArr['Name'])) {
                $folder_id = $documentArr['FolderID'];
            } else {
                $this->Folder->save($folderArr);
                $folder_id = $this->Folder->getLastInsertId();
            }

            //Saving Documents Table
            $this->loadModel('Document');
            if (!empty($documentArr['FileName']) && ($documentArr['FileName']['error'] == 0)) {
                $filename = $documentArr['FileName']['name'];
            }
            // make document array and save document
            $this->Document->create();
            $this->Document->set('FolderID', $folder_id);
            $this->Document->set('FileName', $filename);
            $this->Document->set('ProjectID', $folderArr['ProjectID']);
            $this->Document->set('Version', VERSION);
            $this->Document->set('Description', $documentArr['Description']);
            $this->Document->set('DocumentDate', $documentArr['DocumentDate']);
            $this->Document->set('UploadedDate', date('Y-m-d H:i:s'));
            $this->Document->set('UploadedBy', $memberId);
            $this->Document->set('Status', 1);

            if ($this->Document->save()) {
                //Update document file   
                $document_id = $this->Document->getLastInsertId();
                $document_path = $this->create_document_directory($folderArr['ProjectID']);
                $document_file_name = $this->document_file_name($this->data['Document']['FileName']['name']);
                move_uploaded_file($this->data['Document']['FileName']['tmp_name'], $document_path . $document_file_name);
                $full_url = Router::url('/documentfiles/project_' . $folderArr['ProjectID'] . '/' . $document_file_name, true);
                $updateDocumentArr['id'] = $document_id;
                $updateDocumentArr['FileName'] = $full_url;
                $updateDocumentArr['ServerFileName'] = $document_file_name;
                $this->Document->save($updateDocumentArr);
                //Saving DocumentHistory Table
                $this->loadModel('DocumentHistory');
                $this->DocumentHistory->create();
                $this->DocumentHistory->set('DocumentID', $document_id);
                $this->DocumentHistory->set('DocumentName', $documentArr['Description']);
                $this->DocumentHistory->set('ProjectID', $folderArr['ProjectID']);
                $this->DocumentHistory->set('Date', date('Y-m-d H:i:s'));
                $this->DocumentHistory->set('Action', 'new_document');
                $this->DocumentHistory->set('Initiator', $memberId);
                $this->DocumentHistory->set('NewValue', $documentArr['Description']);
                $this->DocumentHistory->set('Status', 1);
                $this->DocumentHistory->useTable = 'DocumentHistory';

                if ($this->DocumentHistory->save()) {
                    //Saving DocumentOwner Table
                    $this->loadModel('DocumentOwner');
                    $this->DocumentOwner->create();
                    $this->DocumentOwner->set('AddedDate', date('Y-m-d H:i:s'));
                    $this->DocumentOwner->set('DocumentID', $document_id);
                    $this->DocumentOwner->set('MemberID', $memberId);
                    $this->DocumentOwner->save();

                    //Saving ProjectFeed Table        
                    $this->loadModel('ProjectFeed');
                    $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                    $comment = $member_name . " has uploaded the document <strong>" . $filename . "</strong>";
                    $this->ProjectFeed->create();
                    $this->ProjectFeed->set('Date', date('Y-m-d H:i:s'));
                    $this->ProjectFeed->set('FeedVersion', 2);
                    $this->ProjectFeed->set('ProjectID', $folderArr['ProjectID']);
                    $this->ProjectFeed->set('InitiatorID', $memberId);
                    $this->ProjectFeed->set('type', 'new_document');
                    $this->ProjectFeed->set('ResourceID', $document_id);
                    $this->ProjectFeed->set('ResourceName', $member_name);
                    $this->ProjectFeed->set('Title', $comment);
                    $this->ProjectFeed->save();
                }
            }
            $this->Session->setFlash("Document created successfully", 'success_message');
            $this->redirect(array('controller' => 'documents', 'action' => 'index'));
        }
        $projects = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
    }

    public function NewFolder() {

        if ($this->request->is('post')) {
            //Saving Folder Table  
            $this->loadModel('Folder');
            $memberId = $this->Session->read('Auth.User.MemberID');
            $folderArr = $this->request->data['Document'];
            $folderArr['CreatedBy'] = $memberId;
            if ($this->Folder->save($folderArr)) {
                //Saving ProjectFeed Table  
                $folder_id = $this->Folder->getLastInsertId();
                $this->loadModel('ProjectFeed');
                $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                $comment = $member_name . " has created the folder <strong>" . $folderArr['Name'] . "</strong>";
                $this->ProjectFeed->create();
                $this->ProjectFeed->set('Date', date('Y-m-d H:i:s'));
                $this->ProjectFeed->set('FeedVersion', 2);
                $this->ProjectFeed->set('ProjectID', $folderArr['ProjectID']);
                $this->ProjectFeed->set('InitiatorID', $memberId);
                $this->ProjectFeed->set('type', 'new_folder');
                $this->ProjectFeed->set('ResourceID', $folder_id);
                $this->ProjectFeed->set('ResourceName', $folderArr['Name']);
                $this->ProjectFeed->set('Title', $comment);
                $this->ProjectFeed->save();
            }
            $this->Session->setFlash("Folder created successfully", 'success_message');
            $this->redirect(array('controller' => 'documents', 'action' => 'index'));
        }
        $projects = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
    }

    public function rename($id = NULL) {
        $this->loadModel('Folder');
        $this->loadModel('ProjectFeed');
        if ($this->request->is('post')) {
            $getFolder = $this->Custom->get_folders_by_id('first', ['FolderID', 'ProjectID', 'Name'], $id, -1);
            if (!empty($getFolder)) {
                $folderArr = $this->request->data['Folder'];
                $this->Folder->save($folderArr);
                $memberId = $this->Session->read('Auth.User.MemberID');
                $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                $comment = $member_name . " has changed the folder name <strong>" . $getFolder['Folder']['Name'] . "</strong>";
                //Saving ProjectFeed Table
                $this->ProjectFeed->create();
                $this->ProjectFeed->set('Date', date('Y-m-d H:i:s'));
                $this->ProjectFeed->set('FeedVersion', 2);
                $this->ProjectFeed->set('ProjectID', $getFolder['Folder']['ProjectID']);
                $this->ProjectFeed->set('InitiatorID', $memberId);
                $this->ProjectFeed->set('type', 'change_folder_name');
                $this->ProjectFeed->set('ResourceID', $id);
                $this->ProjectFeed->set('ResourceName', $getFolder['Folder']['Name']);
                $this->ProjectFeed->set('Title', $comment);
                $this->ProjectFeed->save();
            }
            $this->Session->setFlash("Folder renamed successfully", 'success_message');
            $this->redirect(array('controller' => 'documents', 'action' => 'index'));
        }
        $getFolder = $this->Custom->get_folders_by_id('first', ['FolderID', 'Name'], $id, -1);
        $this->set('folderData', $getFolder);
    }

    public function delete($id = NULL) {
        $this->loadModel('Folder');
        $this->loadModel('ProjectFeed');
        $getFolder = $this->Custom->get_folders_by_id('first', ['FolderID', 'ProjectID', 'Name'], $id, -1);
        if (!empty($getFolder)) {
            $folderArr['FolderID'] = $id;
            $folderArr['Deleted'] = TRUE;
            $this->Folder->save($folderArr);
            $memberId = $this->Session->read('Auth.User.MemberID');
            $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
            $comment = $member_name . " has deleted the folder <strong>" . $getFolder['Folder']['Name'] . "</strong>";

            //Saving ProjectFeed Table
            $this->ProjectFeed->create();
            $this->ProjectFeed->set('Date', date('Y-m-d H:i:s'));
            $this->ProjectFeed->set('FeedVersion', 2);
            $this->ProjectFeed->set('ProjectID', $getFolder['Folder']['ProjectID']);
            $this->ProjectFeed->set('InitiatorID', $memberId);
            $this->ProjectFeed->set('type', 'delete_folder');
            $this->ProjectFeed->set('ResourceID', $id);
            $this->ProjectFeed->set('ResourceName', $getFolder['Folder']['Name']);
            $this->ProjectFeed->set('Title', $comment);
            $this->ProjectFeed->save();
            $this->Session->setFlash("Folder deleted successfully", 'success_message');
            $this->redirect(array('controller' => 'documents', 'action' => 'index'));
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

    public function send_document($doucment_id, $to) {

        $document = $this->Custom->get_documents('first', $doucment_id, ['FileName', 'ServerFileName'], -1);
        if (!empty($document)) {
            $ServerFileName = $document['Document']['ServerFileName'];
            $FileName = $document['Document']['FileName'];
            $subject = "Document";
            $path = "http://gbc.projectengineer.net/a20001/download.php?file_name=" . $ServerFileName;
            $txt = "<h2>Following link for download the image...</h2><br></br><a href=" . $path . ">Download</a>";
            if (mail($to, $subject, $txt, $this->email_header())) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function ajaxRequest() {
        if ($this->request->is('ajax')) {
            $status = STATUS_CODE;
            $html = '';
            if ($this->data['action'] == 'getfolder') {
                $getFolder = $this->Custom->get_folders_by_project('list', ['FolderID', 'Name'], $this->data['project_id']);
                $html = '<div class="ionic-form"><label for="ProjectName">Folder</label><div class="col-lg-10"><select onchange="folder_selection(this)" name="data[Document][FolderID]">';
                $html .= '<option value="">New Folder</option>';
                if (!empty($getFolder)) {
                    foreach ($getFolder as $key => $title) {
                        $html .= '<option value="' . $key . '">' . $title . '</option>';
                    }
                } else {
                    $status = 201;
                }
                $html .= '</select></div></div><div id="folder_manual"></div>';
            } else if ($this->data['action'] == 'sendDocument') {
                if ($this->Custom->validate_email($this->request->data['email'])) {
                    $doucment_id = $this->request->data['id'];
                    $email = $this->request->data['email'];
                    if ($this->send_document($doucment_id, $email)) {
                        $html .= '<div class="form-group" style="color:green;">Document send successfully...</div>';
                    } else {
                        $status = 201;
                        $html .= '<div class="form-group" style="color:green;">Email not send. Please try later...</div>';
                    }
                } else {
                    $status = 201;
                    $html = 'Please enter valid email';
                }
            }
            $this->Custom->send($status, $html);
        }
    }

    public function delete_document_data() {
        if ($this->request->is('ajax')) {
            $this->Document->id = $this->data['id'];
            if ($this->Document->exists()) {
                $this->Document->delete($this->data['id'], true);
                $this->Common->remove_dir('documentfiles/project_' . $this->data['project_id']);
                $this->Custom->send(200, 'Deleted successfully..');
            } else {
                $this->Custom->send(420, 'Id not exist');
            }
        }
    }

    public function download_files($id = null) {
        $document = $this->Custom->get_documents('first', $id, ['FileName', 'ServerFileName', 'ProjectID'], -1);
        $path = 'documentfiles/project_' . $document['Document']['ProjectID'] . '/' . $document['Document']['ServerFileName'];
        if (file_exists($path)) {
            $this->Common->output_file($path, '' . $document['Document']['ServerFileName'] . '', 'text/plain');
        }
    }

    function clear_sorting_project() {
        $this->Session->delete('Userdefined');
        $this->redirect(array('controller' => 'documents', 'action' => 'index'));
    }

    public function add_forum($folder_id = false, $project_id = false) {
        $this->loadModel('Forum');
        $next_thread = $this->NextThread('documents');
        if ($this->request->is('post')) {
            if ($this->Forum->validates()) {
                if ($this->Forum->save($this->request->data)) {
                    $forum_id = $this->Forum->getLastInsertId();
                    $NewValue = $this->request->data['Forum']['Message'];
                    $comment = "has posted new document forum";
                    $this->create_project_feed($project_id, 'new_document_forum', $forum_id, $next_thread, $NewValue, $comment);
                    $this->Session->setFlash("Document forum created successfully...", 'success_message');
                    $this->redirect(array('controller' => 'documents', 'action' => 'view_forum', $next_thread));
                }
            }
        }
        $get_projects = $this->Custom->get_projects('first', $project_id, ['ProjectID', 'Name'], 0);
        $get_documents = $this->Custom->get_folders_by_id('first', ['FolderID', 'Name'], $folder_id, 0);
        $this->set('get_projects', $get_projects);
        $this->set('get_documents', $get_documents);
        $this->set('Thread', $next_thread);
    }

    public function view_forum($id = null) {
        $this->loadModel('Forum');
        $forumData = $this->Forum->find('all', ['contain' => [
                'User' => [
                    'fields' => ['User.username', 'User.FirstName', 'User.LastName'],
                ],
            ],
            'conditions' => ['Forum.type' => 'documents', 'Forum.Thread' => $id],
            'order' => ['Forum.PostedDate' => 'ASC']
                ]
        );

        $this->set('forumData', $forumData);
        $this->set('forum_thread', $id);
    }

    function reply_forum($id, $thread) {
        $this->loadModel('Forum');
        $next_thread = $this->NextThread('documents');
        $memberId = $this->Session->read('Auth.User.MemberID');
        if (!empty($this->data)) {
            $this->Forum->set($this->data);
            if ($this->Forum->validates()) {
                $this->replySubmitted();
                $this->Session->setFlash(__('Forum submitted successfully...'));
                $this->redirect(array('controller' => 'documents', 'action' => 'view_forum', $thread, $id));
            }
        }

        $this->Forum->recursive = 0;
        $this->data = $this->Forum->find('first', ['contain' => [
                'User' => [
                    'fields' => ['User.MemberID', 'User.FirstName', 'User.LastName'],
                ],
            ],
            'conditions' => ['Forum.ForumPostID' => $id]
                ]
        );
        $this->set('forumData', $this->data);
        $this->set('Thread', $next_thread);
    }

    private function replySubmitted() {
        $this->loadModel('User');
        $this->loadModel('Forum');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $forumPostData = $this->request->data['Forum'];
        $forumPostData['PostedBy'] = $memberId;
        if ($this->Forum->save($forumPostData)) {

            $forum_id = $this->Forum->getLastInsertId();

            /* $forum_invite = $this->request->data['forum_invite'];
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
              } */

            $NewValue = $this->request->data['Forum']['Message'];
            $subject = str_replace("Re: ", "", $this->request->data['Forum']['Subject']);
            $comment = "replied on the forum <strong>" . $subject . "</strong>";
            $this->create_project_feed($forumPostData['ProjectID'], 'reply_document_forum', $forum_id, $forumPostData['Thread'], $forumPostData['Message'], $comment);
        }
    }

}
