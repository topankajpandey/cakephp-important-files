<?php

ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

App::import('ImageResize', 'Controller/Component/ImageResizeComponent.php');
App::import("Vendor", "resize/ImageResize");

class GalleriesController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('login', 'signup', 'forgot', 'reset', 'verification'));
    }

    public function index() {
        $this->set('refine_project_sidebar', true);
        $this->loadModel('Project');
        $this->loadModel('ProjectMember');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $PID = $this->Custom->getExistMemberInProject($memberId);
        $projects = [];
        if ($this->request->is('post') && !empty($this->data['filter_project_id'])) {
            $project_id = $this->request->data['filter_project_id'];
            $this->set('set_project', $this->data['filter_project_id']);
            $projects = $this->Project->find('all', array(
                'contain' => array(
                    'Gallery' => array(
                        'Photo',
                        'conditions' => array(
                            array('Gallery.Deleted' => 0)
                        ),
                        'Forum',
                        'fields' => ['Gallery.GalleryID', 'Gallery.Name', 'Gallery.CreatedDate'],
                    ),
                    'Forum',
                ),
                'conditions' => array('Project.ProjectID' => $project_id),
                    )
            );
        } else {
            $session_project_id = $this->Session->read('Userdefined.project_id');
            if (!empty($PID)) {
                $conditionsBefore = array('Project.ProjectID IN' => $PID, 'Project.Deleted' => 0, 'Project.Archived' => 0);
                $conditionAfter = $this->Custom->checkSessionProject($session_project_id, $conditionsBefore);
                $projects = $this->Project->find('all', array(
                    'contain' => array(
                        'Gallery' => array(
                            'Photo',
                            'conditions' => array(
                                array('Gallery.Deleted' => 0),
                            ),
                            'Forum',
                            'fields' => ['Gallery.GalleryID', 'Gallery.Name', 'Gallery.CreatedDate'],
                        )
                        
                    ),
                    'conditions' => $conditionAfter,
                        )
                );
            }
        }
        //debug($projects); die;
        $projectList = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
        $this->set('projectList', $projectList);
    }

    public function add_forum($gallery_id = false, $project_id = false) {
        $this->loadModel('Forum');
        $next_thread = $this->NextThread('galleries');
        if ($this->request->is('post')) {
            if ($this->Forum->validates()) {
                if ($this->Forum->save($this->request->data)) {
                    $forum_id = $this->Forum->getLastInsertId();
                    $NewValue = $this->request->data['Forum']['Message'];
                    $comment = "has posted new gallery forum";
                    $this->create_project_feed($project_id, 'new_gallery_forum', $forum_id, $next_thread, $NewValue, $comment);
                    $this->Session->setFlash("Gallery forum created successfully...", 'success_message');
                    $this->redirect(array('controller' => 'galleries', 'action' => 'view_forum', $next_thread));
                }
            }
        }
        $get_projects = $this->Custom->get_projects('first', $project_id, ['ProjectID', 'Name'], 0);
        $get_galleries = $this->Custom->get_galleries_by_id('first', ['GalleryID', 'Name'], $gallery_id, 0);
        $this->set('get_projects', $get_projects);
        $this->set('get_galleries', $get_galleries);
        $this->set('Thread', $next_thread);
    }

    public function view_forum($id = null) {
        $this->loadModel('Forum');
        $forumData = $this->Forum->find('all', ['contain' => [
                'User' => [
                    'fields' => ['User.username', 'User.FirstName', 'User.LastName'],
                ],
            ],
            'conditions' => ['Forum.type' => 'galleries', 'Forum.Thread' => $id],
            'order' => ['Forum.PostedDate' => 'ASC']
                ]
        );

        $this->set('forumData', $forumData);
        $this->set('forum_thread', $id);
    }

    function reply_forum($id, $thread) {
        $this->loadModel('Forum');
        $next_thread = $this->NextThread('galleries');
        $memberId = $this->Session->read('Auth.User.MemberID');
        if (!empty($this->data)) {
            $this->Forum->set($this->data);
            if ($this->Forum->validates()) {
                $this->replySubmitted();
                $this->Session->setFlash(__('Forum submitted successfully...'));
                $this->redirect(array('controller' => 'galleries', 'action' => 'view_forum', $thread, $id));
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

    public function list_bydate() {
        $this->loadModel('Gallery');
        $this->loadModel('Project');

        $gallerylisting = $this->Gallery->find('all', array('order' => array('Gallery.GalleryID DESC')));

        $this->set('gallerylisting', $gallerylisting);
    }

    public function add_gallery() {

        $this->loadModel('Gallery');
        $memberId = $this->Session->read('Auth.User.MemberID');
        if ($this->request->is('Post')) {
            $PhotoGalleryArr = $this->request->data['Gallery'];
            $PhotoGalleryArr['CreatedBy'] = $memberId;
            if ($this->Gallery->save($PhotoGalleryArr)) {
                $NewGalleryID = $this->Gallery->getLastInsertId();
                $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));

                //Saving PhotoGallery History        
                $this->loadModel('PhotoGalleryHistory');
                $this->PhotoGalleryHistory->create();
                $this->PhotoGalleryHistory->set('Date', date('Y-m-d H:i:s'));
                $this->PhotoGalleryHistory->set('PhotoGalleryID', $NewGalleryID);
                $this->PhotoGalleryHistory->set('PhotoGalleryName', $PhotoGalleryArr['Name']);
                $this->PhotoGalleryHistory->set('ProjectID', $PhotoGalleryArr['ProjectID']);
                $this->PhotoGalleryHistory->set('ChangedBy', $memberId);
                $this->PhotoGalleryHistory->set('ChangeCode', 1);
                $this->PhotoGalleryHistory->save();

                //Saving ProjectFeed for add gallery 
                $this->loadModel('ProjectFeed');
                $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                $comment = $member_name . " hhas created the photo gallery <strong>" . $PhotoGalleryArr['Name'] . "</strong>";
                $this->ProjectFeed->create();
                $this->ProjectFeed->set('Date', date('Y-m-d H:i:s'));
                $this->ProjectFeed->set('FeedVersion', 2);
                $this->ProjectFeed->set('ProjectID', $PhotoGalleryArr['ProjectID']);
                $this->ProjectFeed->set('InitiatorID', $memberId);
                $this->ProjectFeed->set('type', 'new_photo_gallery');
                $this->ProjectFeed->set('ResourceID', $NewGalleryID);
                $this->ProjectFeed->set('ResourceName', $member_name);
                $this->ProjectFeed->set('Title', $comment);
                $this->ProjectFeed->save();
                $this->Session->setFlash("The gallery has been created successfully", 'success_message');
                $this->redirect(array('controller' => 'galleries', 'action' => 'index'));
            } else {
                $this->Session->setFlash("Please correct the errors", 'error_message');
            }
        }
        $projects = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
    }

    public function get_gallery_photo($gallery_id, $memberId) {
        $this->loadModel('Photo');
        $gallerydetails = $this->Photo->find('all', array('conditions' => array('Photo.GalleryID' => $gallery_id, 'Photo.Deleted' => 0)));
        $setGalleryPhotoArr = [];
        if (!empty($gallerydetails)) {
            foreach ($gallerydetails as $key => $value) {
                $setGalleryPhotoArr[] = array(
                    'PhotoID' => $value['Photo']['PhotoID'],
                    'name' => $value['Photo']['FileName'],
                    'url' => $this->get_gallery_photo_url($memberId, $gallery_id, 'URL')['full_url'] . $value['Photo']['FileName'],
                    'thumbnailUrl' => $this->get_gallery_photo_url($memberId, $gallery_id, 'URL')['full_thumb_url'] . 'thumb_' . $value['Photo']['FileName'],
                    'deleteUrl' => $this->webroot . 'galleries/' . $memberId . '/' . $value['Photo']['FileName'],
                    'deleteUrl' => $this->webroot . 'galleries/delete_photo/' . $value['Photo']['PhotoID'],
                    'deleteType' => 'DELETE',
                );
            }
        }
        return $setGalleryPhotoArr;
    }

    private function create_user_folder_inside_gallery($memberId, $gallery_id) {
        if (!is_dir('galleries/user_' . $memberId . '/gallery_' . $gallery_id)) {
            mkdir('galleries/user_' . $memberId . '/gallery_' . $gallery_id, 0777, true);
            mkdir('galleries/user_' . $memberId . '/gallery_' . $gallery_id . '/thumb', 0777, true);
        }
    }

    private function get_gallery_photo_url($memberId, $gallery_id, $type) {
        $path = [];
        if ($type == 'PATH') {
            $path['full_path'] = 'galleries/user_' . $memberId . '/gallery_' . $gallery_id . '/';
            $path['full_thumb_path'] = 'galleries/user_' . $memberId . '/gallery_' . $gallery_id . '/thumb/';
        } else if ($type == 'URL') {
            $path['full_url'] = $this->webroot . 'galleries/user_' . $memberId . '/gallery_' . $gallery_id . '/';
            $path['full_thumb_url'] = $this->webroot . 'galleries/user_' . $memberId . '/gallery_' . $gallery_id . '/thumb/';
        }
        return $path;
    }

    public function edit_gallery($id = null) {
        $this->loadModel('Gallery');
        $this->loadModel('Photo');
        $gallery_id = base64_decode($id);
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->create_user_folder_inside_gallery($memberId, $gallery_id);
        if ($this->request->is('ajax')) {
            if (!empty($_FILES)) {

                $this->get_gallery_photo_url($memberId, $id, 'PATH')['full_path'];
                $config["generate_image_file"] = true;
                $config["generate_thumbnails"] = true;
                $config["image_max_size"] = 500;
                $config["thumbnail_size"] = 200;
                $config["thumbnail_prefix"] = "thumb_";
                $config["destination_folder"] = $this->get_gallery_photo_url($memberId, $id, 'PATH')['full_path'];
                $config["thumbnail_destination_folder"] = $this->get_gallery_photo_url($memberId, $id, 'PATH')['full_thumb_path'];
                $config["upload_url"] = $this->webroot . 'galleries/user_' . $memberId . '/gallery_' . $id;
                $config["quality"] = 100;
                $config["random_file_name"] = true;
                $config["file_data"] = $_FILES["files"];
                $resizeImage = new ImageResize($config);
                $response = $resizeImage->resize();

                $photoArr['Photo']['GalleryID'] = $id;
                $photoArr['Photo']['DateUploaded'] = date('Y-m-d H:i:s');
                $photoArr['Photo']['FileName'] = $response['images'][0];
                $this->Photo->save($photoArr);
                $photo_id = $this->Photo->getLastInsertId();

                $path_info = pathinfo($_FILES['files']["name"][0]);
                $ext = "." . $path_info["extension"];
                $ServerFileName = sprintf("%09d", $photo_id) . $ext;
                $updateArr = array('id' => $photo_id, 'ServerFileName' => $ServerFileName);
                $this->Photo->save($updateArr);

                $jsonArr[] = array(
                    'name' => $response['images'][0],
                    'url' => $this->get_gallery_photo_url($memberId, $id, 'URL')['full_url'] . $response['images'][0],
                    'thumbnailUrl' => $this->get_gallery_photo_url($memberId, $id, 'URL')['full_thumb_url'] . 'thumb_' . $response['images'][0],
                    'deleteUrl' => $this->webroot . 'galleries/delete_photo/' . $photo_id,
                    'deleteType' => 'DELETE',
                );
                echo json_encode(array('files' => $jsonArr));
            } else {
                $jsonArr = $this->get_gallery_photo($id, $memberId);
                echo json_encode(array('files' => $jsonArr));
            }
            $this->autoRender = false;
        }
        $this->set('gid', $gallery_id);
        $this->data = $this->Gallery->find('first', array('conditions' => array('Gallery.GalleryID' => $id, 'Gallery.Deleted' => 0)));
        $projects = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
    }

    public function gallery_ajax() {
        if ($this->request->is('ajax')) {
            if (($this->request->data['action'] == 'add_caption') && (!empty($this->request->data['Photo']['Caption']))) {
                $this->loadModel('Photo');
                $photoArr = $this->request->data['Photo'];
                if ($this->Photo->save($photoArr)) {
                    $this->Custom->send(200, 'Caption added successfully');
                } else {
                    $this->Custom->send(201, '<p style="color:red;">Problem to update caption. Please try later<p>');
                }
            } else {
                $this->Custom->send(201, '<p style="color:red;">Please enter the caption<p>');
            }
        }
        $this->autoRender = false;
    }

    public function delete_photo($photo_id) {

        $this->autoRender = false;
        $this->loadModel('Photo');
        $this->Photo->id = $photo_id;
        $memberId = $this->Session->read('Auth.User.MemberID');
        if ($this->Photo->exists()) {
            $this->Photo->query("update Photos set Deleted = 1 WHERE PhotoID = '$photo_id'");
            /* $gallerydetails = $this->Photo->find('first', array('conditions' => array('Photo.PhotoID' => $photo_id)));
              $this->Photo->delete($photo_id, true);
              unlink('galleries/'.$memberId.'/'.$gallerydetails['Photo']['FileName']);
              unlink('galleries/'.$memberId.'/thumb/thumb_'.$gallerydetails['Photo']['FileName']); */
            echo true;
        }
        $this->autoRender = false;
    }

    public function new_task() {
        $userDetail = $this->Session->read('Auth.User');
        $userID = $userDetail['MemberID'];
        $firstName = $userDetail['FirstName'];
        $lastName = $userDetail['LastName'];
        if ($this->request->is('Post')) {

            $projectid = $this->request->data['ProjectID'];
            $assignedto = $this->request->data['AssignedTo'];

            $this->Schedule->set('ProjectID', $projectid);
            $this->Schedule->set('AssignedTo', $assignedto);
            $this->Schedule->set('AssignedBy', $userID);

            $this->Schedule->set($this->request->data);

            if ($this->Schedule->save($this->request->data)) {
                $NewTaskID = $this->Schedule->getLastInsertId();
                $this->Session->setFlash("The task has been created successfully", 'success_message');
                $this->redirect(array('controller' => 'dashboard'));
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
            }
        }
    }

    public function view_gallery($gid) {
        $this->loadModel('Gallery');
        $this->loadModel('GalleryPhoto');

        $gallerydetails = $this->Gallery->find('first', array('conditions' => array('Gallery.GalleryID' => $gid)));
        $galleryid = $gallerydetails['Gallery']['GalleryID'];
        $photodetails = $this->GalleryPhoto->find('all', array('conditions' => array('GalleryPhoto.GalleryID' => $galleryid)));
        $this->set('gallerydetails', $gallerydetails);
        $this->set('photodetails', $photodetails);
    }

    public function edit_galleryname($gid) {

        $this->loadModel('Gallery');
        $this->loadModel('GalleryPhoto');
        $this->set('gid', $gid);
        if ($this->request->is('Post')) {
            $name = $this->request->data['Galleries']['PGName'];
            if ($this->Gallery->updateAll(array('Name' => "'$name'"), array('GalleryID' => $gid))) {
                $this->Session->setFlash("Caption has been saved successfully", 'success_message');
                $this->redirect(array('controller' => 'galleries', 'action' => 'edit_gallery', $gid));
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
            }
        }
    }

    public function comment_task($tid) {
        $this->loadModel('TaskComment');
        if ($this->request->is('Post')) {

            $commentid = $this->request->data['TaskCommentID'];
            if ($this->TaskComment->save($this->request->data)) {
                $NewTaskCommentID = $this->Schedule->getLastInsertId();
                $this->Session->setFlash("The task comment has been created successfully", 'success_message');
                $this->redirect(array('controller' => 'view_task'));
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
            }
        }
    }

    public function delete_gallery($gid) {
        $this->loadModel('Photo');
        $this->Gallery->query("update PhotoGalleries set Deleted = 1 WHERE GalleryID = '" . $gid . "'");
        $this->Photo->query("update Photos set Deleted = 1 WHERE GalleryID = '" . $gid . "'");
        $this->Session->setFlash("Gallery has been removed", 'success_message');
        $this->redirect(array('controller' => 'galleries', 'action' => 'index'));
    }

    public function delete_galleryphoto($pid) {
        $this->loadModel('GalleryPhoto');
        $galleryid = $this->GalleryPhoto->find('first', array('conditions' => array('GalleryPhoto.PhotoID' => $pid)));
        $gid = $galleryid['GalleryPhoto']['GalleryID'];
        $this->GalleryPhoto->query("DELETE FROM Photos WHERE PhotoID = '" . $pid . "'");
        $this->Session->setFlash("Gallery Photo has been removed from the project.", 'success_message');
        $this->redirect(array('controller' => 'galleries', 'action' => 'edit_gallery', $gid));
    }

}
