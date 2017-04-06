<?php
ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
        CONST STATUS_CODE = 200;
        CONST VERSION = 1;

class DocumentsController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form', 'Session', 'Custom');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('login', 'signup', 'forgot', 'reset', 'verification'));
    }

    public function index() {
        $this->loadModel('Project');
        $this->loadModel('ProjectMember');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $PID = $this->Custom->getExistMemberInProject($memberId);
        if ($this->request->is('post')) {
            $projects = $this->Project->find('all', array(
                'contain' => array(
                    'Folders' => array(
                        'conditions' => array(
                            array('Folders.Deleted' => 0)
                        ),
                        'Documents'
                    )
                ),
                'conditions' => array('Project.ProjectID IN' => $PID, 'Project.ProjectID' => $this->request->data['project_id'])
                )
            );
            $this->Session->write('Userdefined.project_id', $this->request->data['project_id']);
        } else {
            
            $session_project_id = $this->Session->read('Userdefined.project_id');
            $conditionsBefore =  array('Project.ProjectID IN' => $PID, 'Project.Deleted' => 0, 'Project.Archived' => 0);
            $conditionAfter = $this->Custom->checkSessionProject($session_project_id, $conditionsBefore);
            
            $projects = $this->Project->find('all', array(
                'contain' => array(
                    'Folders' => array(
                        'conditions' => array(
                            array('Folders.Deleted' => 0)
                        ),
                        'Documents'
                    )
                ),
                'conditions' => $conditionAfter
                )
            );
        }
        $projectList = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
        $this->set('projectList', $projectList);
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

            $documentArr['FolderID'] = $folder_id;
            $documentArr['FileName'] = $filename;
            $documentArr['ProjectID'] = $folderArr['ProjectID'];
            $documentArr['Version'] = VERSION;
            $documentArr['UploadedBy'] = $memberId;
            $documentArr['Status'] = 1;

            if ($this->Document->save($documentArr)) {
                //Saving DocumentHistory Table
                $this->loadModel('DocumentHistory');
                $document_id = $this->Document->getLastInsertId();
                $documentHistory['DocumentID'] = $document_id;
                $documentHistory['DocumentName'] = $documentArr['Description'];
                $documentHistory['ProjectID'] = $folderArr['ProjectID'];
                $documentHistory['Date'] = date('Y-m-d H:i:s');
                $documentHistory['Action'] = 'new_document';
                $documentHistory['Initiator'] = $memberId;
                $documentHistory['NewValue'] = $documentArr['Description'];
                $this->DocumentHistory->useTable = 'DocumentHistory';

                if ($this->DocumentHistory->save($documentHistory)) {

                    //Saving DocumentOwner Table
                    $this->loadModel('DocumentOwner');
                    $documentOwner['AddedDate'] = date('Y-m-d H:i:s');
                    $documentOwner['DocumentID'] = $document_id;
                    $documentOwner['MemberID'] = $memberId;
                    $this->DocumentOwner->save($documentOwner);

                    //Saving ProjectFeed Table        
                    $this->loadModel('ProjectFeed');
                    $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                    $comment = $member_name . " has uploaded the document <strong>" . $filename . "</strong>";
                    $projectFeed['Date'] = date('Y-m-d H:i:s');
                    $projectFeed['FeedVersion'] = 2;
                    $projectFeed['ProjectID'] = $folderArr['ProjectID'];
                    $projectFeed['InitiatorID'] = $memberId;
                    $projectFeed['type'] = 'new_document';
                    $projectFeed['ResourceID'] = $document_id;
                    $projectFeed['ResourceName'] = $member_name;
                    $projectFeed['Title'] = $comment;

                    if ($this->ProjectFeed->save($projectFeed)) {
                        $project_feed_id = $this->ProjectFeed->getLastInsertId();
                        $NewFileName = sprintf("%09d", $document_id) . ".pm";
                        $Path = WWW_ROOT . 'User/UserDocuments/';
                        $documentArr = $this->request->data['Document'];
                        if (!empty($documentArr['FileName']) && ($documentArr['FileName']['error'] == 0)) {
                            move_uploaded_file($documentArr['FileName']['tmp_name'], $Path . $NewFileName);
                        }
                        $updateDocumentArr['id'] = $document_id;
                        $updateDocumentArr['ServerFileName'] = $NewFileName;
                        $this->Document->save($updateDocumentArr);
                    }
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
                $projectFeed['Date'] = date('Y-m-d H:i:s');
                $projectFeed['FeedVersion'] = 2;
                $projectFeed['ProjectID'] = $folderArr['ProjectID'];
                $projectFeed['InitiatorID'] = $memberId;
                $projectFeed['type'] = 'new_folder';
                $projectFeed['ResourceID'] = $folder_id;
                $projectFeed['ResourceName'] = $folderArr['Name'];
                $projectFeed['Title'] = $comment;
                $this->ProjectFeed->save($projectFeed);
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
                $projectFeed['Date'] = date('Y-m-d H:i:s');
                $projectFeed['FeedVersion'] = 2;
                $projectFeed['ProjectID'] = $getFolder['Folder']['ProjectID'];
                $projectFeed['InitiatorID'] = $memberId;
                $projectFeed['type'] = 'change_folder_name';
                $projectFeed['ResourceID'] = $id;
                $projectFeed['ResourceName'] = $getFolder['Folder']['Name'];
                $projectFeed['Title'] = $comment;
                $this->ProjectFeed->save($projectFeed);
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
            $projectFeed['Date'] = date('Y-m-d H:i:s');
            $projectFeed['FeedVersion'] = 2;
            $projectFeed['ProjectID'] = $getFolder['Folder']['ProjectID'];
            $projectFeed['InitiatorID'] = $memberId;
            $projectFeed['type'] = 'delete_folder';
            $projectFeed['ResourceID'] = $id;
            $projectFeed['ResourceName'] = $getFolder['Folder']['Name'];
            $projectFeed['Title'] = $comment;
            $this->ProjectFeed->save($projectFeed);
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
                $html = '<div class="ionic-form"><label for="ProjectName">Folder</label><select onchange="folder_selection(this)" name="data[Document][FolderID]">';
                $html .= '<option value="">New Folder</option>';
                if (!empty($getFolder)) {
                    foreach ($getFolder as $key => $title) {
                        $html .= '<option value="' . $key . '">' . $title . '</option>';
                    }
                } else {
                    $status = 201;
                }
                $html .= '</select></div><div id="folder_manual"></div>';
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

    function clear_sorting_project() {
        $this->Session->delete('Userdefined');
        $this->redirect(array('controller' => 'documents', 'action' => 'index'));
    }

}
