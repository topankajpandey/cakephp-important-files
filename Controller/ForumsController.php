<?php

ob_start();
App::uses('AppController', 'Controller');
        CONST STATUS_CODE = 201;
        CONST VERSION = 1;

class ForumsController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');

    public function index() {
        $this->loadModel('Forum');
        if ($this->request->is('Post') && !empty($this->request->data['Subject'])) {
            $keyword = trim($this->request->data['Subject']);
            $forumLists = $this->Forum->find('all', array('conditions' => array("OR" => array("Forum.Subject LIKE" => "%$keyword%", "Forum.Message LIKE" => "%$keyword%"))));
        } else {
            $forumLists = $this->Forum->find('all', ['contain' => [
                    'User' => [
                        'fields' => ['User.FirstName', 'User.LastName'],
                    ],
                    'Project' => [
                        'ProjectManager' => ['User'],
                        'fields' => ['Project.Name', 'Project.Description'],
                    ],
                ],
                'conditions' => ['Forum.Archived' => (int) 0, 'Forum.Level' => (int) 0],
                'order' => ['Forum.PostedDate' => 'DESC']
                    ]
            );
        } 
        $this->set('forumLists', $forumLists);
        $condition = ['User.Active' => 1, 'User.Access_Level' => 4];
        $field = ['User.MemberID', 'FirstName', 'LastName'];
        $members = $this->Custom->get_members('all', $condition, $field, 0);
        $projects = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
        $this->set('projectMember', $members);
    }

    private function getNextThread() {
        $this->loadModel('Forum');
        $forumData = $this->Forum->find('first', array(
            'fields' => array('MAX(Forum.Thread) AS MaxThread')
                )
        );
        return ($forumData[0]['MaxThread'] + 1);
    }

    private function check_existing_invitaion($forum_id, $email) {
        $this->loadModel('ForumInvite');
        $count = $this->ForumInvite->find('count', array('recursive' => 0, 'conditions' => array('ForumInvite.ForumID' => $MemberID, 'ForumInvite.Email' => $email)));
        return $count;
    }

    public function submit_forum() {
        if ($this->request->is('ajax')) {
            $this->loadModel('User');
            $status = STATUS_CODE;
            $html = '<div class="alert alert-danger">Problem with submitting forum. Please try later.</div>';
            $this->loadModel('Forum');
            $memberId = $this->Session->read('Auth.User.MemberID');
            $forumPostData = $this->request->data['Fourm'];
            $forum_invite = $this->request->data['forum_invite'];
            $forumPostData['PostedBy'] = $memberId;
            $forumPostData['Thread'] = $this->getNextThread();
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

                $this->loadModel('ProjectFeed');
                $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                $comment = $member_name . " has posted in the project forum";
                $projectFeed['Date'] = date('Y-m-d H:i:s');
                $projectFeed['FeedVersion'] = 2;
                $projectFeed['ProjectID'] = $forumPostData['ProjectID'];
                $projectFeed['InitiatorID'] = $memberId;
                $projectFeed['type'] = 'new_forum_post';
                $projectFeed['ResourceID'] = $forum_id;
                $projectFeed['ResourceID2'] = $this->getNextThread();
                $projectFeed['NewValue'] = $forumPostData['Message'];
                $projectFeed['Title'] = $comment;
                $this->ProjectFeed->save($projectFeed);
                $status = 200;
                $html = '<div class="alert alert-success">Forum successfully submitted</div>';
            }
            $this->Custom->send($status, $html);
        }
    }

    public function viewthread($thread = null) {
        $id = base64_decode($thread);
        $this->loadModel('Forum');

        $forumData = $this->Forum->find('all', ['contain' => [
                'User' => [
                    'fields' => ['User.username', 'User.FirstName', 'User.LastName'],
                ],
            ],
            'conditions' => ['Forum.Thread' => $id],
            'order' => ['Forum.PostedDate' => 'ASC']
                ]
        );

        $this->set('forumData', $forumData);
    }

    private function replySubmitted($data) {
        $this->loadModel('User');
        $this->loadModel('Forum');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $forumPostData = $this->request->data['Forum'];
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

            $this->loadModel('ProjectFeed');
            $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
            $comment = $member_name . " has posted in the project forum";
            $projectFeed['Date'] = date('Y-m-d H:i:s');
            $projectFeed['FeedVersion'] = 2;
            $projectFeed['ProjectID'] = $forumPostData['ProjectID'];
            $projectFeed['InitiatorID'] = $memberId;
            $projectFeed['type'] = 'new_forum_post_reply';
            $projectFeed['ResourceID'] = $forum_id;
            $projectFeed['ResourceID2'] = $forumPostData['Thread'];
            $projectFeed['NewValue'] = $forumPostData['Message'];
            $projectFeed['Title'] = $comment;
            $this->ProjectFeed->save($projectFeed);
        }
    }

    public function replyforum($id = null) {
        $this->loadModel('Forum');
        if (!empty($this->data)) {
            $this->Forum->set($this->data);
            if ($this->Forum->validates()) {
                $thread = base64_encode($this->request->data['Forum']['Thread']);
                $this->replySubmitted($this->request->data);
                $this->Session->setFlash(__('Forum submitted successfully...'));
                $this->redirect(array('controller' => 'forums', 'action' => 'viewthread', $thread));
            }
        }

        $forumPostID = base64_decode($id);
        $this->Forum->recursive = 0;
        $this->data = $this->Forum->find('first', ['contain' => [
                'User' => [
                    'fields' => ['User.MemberID', 'User.FirstName', 'User.LastName'],
                ],
            ],
            'conditions' => ['Forum.ForumPostID' => $forumPostID]
                ]
        );
        $condition = ['User.Active' => 1];
        $field = ['User.MemberID', 'FirstName', 'LastName'];
        $members = $this->Custom->get_members('all', $condition, $field, 0);
        $projects = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
        $this->set('projectMember', $members);
        $this->set('forumData', $this->data);
    }

    public function delete($id = null) {
        $this->autoRender = false;
        $this->loadModel('Forum');
        $this->loadModel('ForumInvite');
        $this->Forum->id = $id;
        if (!$this->Forum->exists()) {
            throw new NotFoundException(__('Invalid forum'));
        }
        if ($this->Forum->delete($id, true)) {
            $this->ForumInvite->query("delete from ForumInvite where ForumID = '$id'");
            $this->Custom->send(200, 'Comment deleted successfully');
        }
        $this->Custom->send(201, 'Problem with deleting comment. Please try later');
    }

}
