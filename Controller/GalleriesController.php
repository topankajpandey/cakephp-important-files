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

        $this->loadModel('Project');
        $this->loadModel('ProjectMember');
        $memberId = $this->Session->read('Auth.User.MemberID');
        $PID = $this->Custom->getExistMemberInProject($memberId);

        if ($this->request->is('post')) {
            $project_id = $this->request->data['project_id'];
            $projects = $this->Project->find('all', array(
                'contain' => array(
                    'Gallery' => array(
                        'Photo',
                        'conditions' => array(
                            array('Gallery.Deleted' => 0)
                        ),
                        'fields' => ['Gallery.GalleryID', 'Gallery.Name', 'Gallery.CreatedDate'],
                    )
                ),
                'conditions' => array('Project.ProjectID' => $project_id),
                    )
            );
        } else {
            $session_project_id = $this->Session->read('Userdefined.project_id');
            $conditionsBefore = array('Project.ProjectID IN' => $PID, 'Project.Deleted' => 0, 'Project.Archived' => 0);
            $conditionAfter = $this->Custom->checkSessionProject($session_project_id, $conditionsBefore);

            $projects = $this->Project->find('all', array(
                'contain' => array(
                    'Gallery' => array(
                        'Photo',
                        'conditions' => array(
                            array('Gallery.Deleted' => 0),
                        ),
                        'fields' => ['Gallery.GalleryID', 'Gallery.Name', 'Gallery.CreatedDate'],
                    )
                ),
                'conditions' => $conditionAfter,
                    )
            );
        }
        //debug($projects); die;
        $projectList = $this->Custom->get_projects('list', NULL, ['ProjectID', 'Name'], -1);
        $this->set('projectData', $projects);
        $this->set('projectList', $projectList);
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

                //Saving ProjectFeed Table        
                $this->loadModel('PhotoGalleryHistory');
                $PhotoGalHist['PhotoGalleryHistory']['Date'] = date('Y-m-d H:i:s');
                $PhotoGalHist['PhotoGalleryHistory']['PhotoGalleryID'] = $NewGalleryID;
                $PhotoGalHist['PhotoGalleryHistory']['PhotoGalleryName'] = $PhotoGalleryArr['Name'];
                $PhotoGalHist['PhotoGalleryHistory']['ProjectID'] = $PhotoGalleryArr['ProjectID'];
                $PhotoGalHist['PhotoGalleryHistory']['ChangedBy'] = $memberId;
                $PhotoGalHist['PhotoGalleryHistory']['ChangeCode'] = 1;
                $this->PhotoGalleryHistory->save($PhotoGalHist);

                //Saving ProjectFeed Table        
                $this->loadModel('ProjectFeed');
                $member_name = $this->Custom->get_member_name($this->Session->read('Auth.User'));
                $comment = $member_name . " hhas created the photo gallery <strong>" . $PhotoGalleryArr['Name'] . "</strong>";
                $projectFeed['Date'] = date('Y-m-d H:i:s');
                $projectFeed['FeedVersion'] = 2;
                $projectFeed['ProjectID'] = $PhotoGalleryArr['ProjectID'];
                $projectFeed['InitiatorID'] = $memberId;
                $projectFeed['type'] = 'new_photo_gallery';
                $projectFeed['ResourceID'] = $NewGalleryID;
                $projectFeed['ResourceName'] = $member_name;
                $projectFeed['Title'] = $comment;
                $this->ProjectFeed->save($projectFeed);

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
                    'thumbnailUrl' => $this->get_gallery_photo_url($memberId, $gallery_id, 'URL')['full_thumb_url'].'thumb_' . $value['Photo']['FileName'],
                    'deleteUrl' => $this->webroot . 'galleries/' . $memberId . '/' . $value['Photo']['FileName'],
                    'deleteUrl' => $this->webroot . 'galleries/delete_photo/' . $value['Photo']['PhotoID'],
                    'deleteType' => 'DELETE',
                );
            }
        }
        return $setGalleryPhotoArr;
    }

    private function create_user_folder_inside_gallery($memberId,$gallery_id) {
        if (!is_dir('galleries/user_' . $memberId.'/gallery_'.$gallery_id)) {
            mkdir('galleries/user_' . $memberId.'/gallery_'.$gallery_id, 0777, true);
            mkdir('galleries/user_' . $memberId.'/gallery_'.$gallery_id.'/thumb', 0777, true);
        }
    }
    
    private function get_gallery_photo_url($memberId, $gallery_id, $type){
        $path = [];
        if($type=='PATH'){
            $path['full_path'] = 'galleries/user_' . $memberId.'/gallery_'.$gallery_id.'/';
            $path['full_thumb_path'] = 'galleries/user_' . $memberId.'/gallery_'.$gallery_id.'/thumb/';
        }else if($type=='URL'){
            $path['full_url'] = $this->webroot.'galleries/user_' . $memberId.'/gallery_'.$gallery_id.'/';
            $path['full_thumb_url'] = $this->webroot.'galleries/user_' . $memberId.'/gallery_'.$gallery_id.'/thumb/';
        }
        return $path;
    }

    public function edit_gallery($id = null) {
        $this->loadModel('Gallery');
        $this->loadModel('Photo');
        $gallery_id = base64_decode($id);
        $memberId = $this->Session->read('Auth.User.MemberID');
        $this->create_user_folder_inside_gallery($memberId,$gallery_id);
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
                $config["upload_url"] = $this->webroot . 'galleries/user_' . $memberId.'/gallery_'.$id;
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
                    'thumbnailUrl' => $this->get_gallery_photo_url($memberId, $id, 'URL')['full_thumb_url'].'thumb_' . $response['images'][0],
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
    
    public function gallery_ajax(){
        if ($this->request->is('ajax')) {
            if( ($this->request->data['action']=='add_caption') && (!empty($this->request->data['Photo']['Caption']))){
                $this->loadModel('Photo');
                $photoArr = $this->request->data['Photo'];
                if($this->Photo->save($photoArr)){
                    $this->Custom->send(200, 'Caption added successfully');
                }else{
                    $this->Custom->send(201, '<p style="color:red;">Problem to update caption. Please try later<p>');
                }
            }else{
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
            /*$gallerydetails = $this->Photo->find('first', array('conditions' => array('Photo.PhotoID' => $photo_id)));
            $this->Photo->delete($photo_id, true);
            unlink('galleries/'.$memberId.'/'.$gallerydetails['Photo']['FileName']);
            unlink('galleries/'.$memberId.'/thumb/thumb_'.$gallerydetails['Photo']['FileName']);*/
            echo true;
        }
        $this->autoRender = false;
    }

    public function gallery_nextpage() {
        $this->loadModel('Gallery');
        $this->loadModel('GalleryPhoto');
        $uploadData = '';


        if ($this->request->is('post')) {

            $lastItem = $this->Gallery->find('first', array('order' => array('Gallery.GalleryID DESC')));
            $galleryid = $lastItem['Gallery']['GalleryID'];
            $galleryname = $lastItem['Gallery']['Name'];
            $this->request->data['DateUploaded'] = date('Y-m-d h:i:s');

            $this->GalleryPhoto->set('GalleryID', $galleryid);
            $filenamez = $this->request->data['Gallery']['file']['name'];
            if (!empty($this->request->data['Gallery']['file']['name'])) {
                $fileName = $this->request->data['Gallery']['file']['name'];
                $uploadPath = 'uploads/files/';
                $uploadFile = $uploadPath . $fileName;
                if (move_uploaded_file($this->request->data['Gallery']['file']['tmp_name'], $uploadFile)) {
                    //$uploadData = $this->GalleryPhoto->newEntity();
                    $uploadData->name = $fileName;
                    $uploadData->path = $uploadPath;
                    $uploadData->created = date("Y-m-d H:i:s");
                    $uploadData->modified = date("Y-m-d H:i:s");

                    $data = array(
                        'GalleryID' => $lastItem['Gallery']['GalleryID'],
                        'DateUploaded' => date('Y-m-d h:i:s'),
                        'FileName' => $filenamez
                    );
                    //  print_r($data);
                    $this->GalleryPhoto->set($data);

                    if ($this->GalleryPhoto->save($data)) {
                        $this->Session->setFlash("File has been uploaded and inserted successfully", 'success_message');
                        $this->redirect(array('controller' => 'galleries/gallery_lastpage'));
                    } else {
                        $this->Session->setFlash("Unable to upload file, please try again.", 'error_message');
                    }
                } else {
                    $this->Session->setFlash("Unable to upload file, please try again.", 'error_message');
                }
            } else {
                $this->Session->setFlash("Please choose a file to upload.", 'error_message');
            }
        }
        $this->set('uploadData', $uploadData);

        $photogalleryname = $this->Gallery->find('first', array('order' => array('Gallery.GalleryID DESC')));
        $this->set('photogalleryname', $photogalleryname);
    }

    public function gallery_lastpage() {
        $this->loadModel('Gallery');
        $this->loadModel('GalleryPhoto');

        $lastphotoItem = $this->GalleryPhoto->find('first', array('order' => array('GalleryPhoto.PhotoID DESC')));

        $photoid = $lastphotoItem['GalleryPhoto']['PhotoID'];

        if ($this->request->is('Post')) {

            $caption = $this->request->data['Galleries']['Caption'];


            if ($this->GalleryPhoto->updateAll(array('Caption' => "'$caption'"), array('PhotoID' => $photoid))) {

                $this->Session->setFlash("The gallery has been created successfully", 'success_message');
                $this->redirect(array('controller' => 'galleries/pg_home'));
            } else {
                $this->Session->setFlash("Please correct the following errors", 'error_message');
            }
        }
        $photogalleryname = $this->Gallery->find('first', array('order' => array('Gallery.GalleryID DESC')));
        $photoname = $this->GalleryPhoto->find('first', array('order' => array('GalleryPhoto.PhotoID DESC')));

        $this->set('photogalleryname', $photogalleryname);
        $this->set('photoname', $photoname);
    }

    public function new_task() {
        $userDetail = $this->Session->read('Auth.User');
        // echo "<pre>";
        // print_r($userDetail);
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
                // echo "<pre>";
                // print_r($this->request->data); exit;
                $NewTaskID = $this->Schedule->getLastInsertId();
                //exit;

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

    public function edit_nextpage($pid) {
        $this->loadModel('Gallery');
        $this->loadModel('GalleryPhoto');

        $g_id = $this->GalleryPhoto->find('first', array('conditions' => array('GalleryPhoto.PhotoID' => $pid)));
        $gallery_id = $g_id['GalleryPhoto']['GalleryID'];
        $this->set('gallery_id', $gallery_id);

        $uploadData = '';


        if ($this->request->is('post')) {

            $lastItem = $this->Gallery->find('first', array('order' => array('Gallery.GalleryID DESC')));
            $galleryid = $lastItem['Gallery']['GalleryID'];
            $galleryname = $lastItem['Gallery']['Name'];
            $this->request->data['DateUploaded'] = date('Y-m-d h:i:s');

            $this->GalleryPhoto->set('GalleryID', $gallery_id);
            $filenamez = $this->request->data['Gallery']['file']['name'];
            if (!empty($this->request->data['Gallery']['file']['name'])) {
                $fileName = $this->request->data['Gallery']['file']['name'];
                $uploadPath = 'uploads/files/';
                $uploadFile = $uploadPath . $fileName;
                if (move_uploaded_file($this->request->data['Gallery']['file']['tmp_name'], $uploadFile)) {
                    //$uploadData = $this->GalleryPhoto->newEntity();
                    $uploadData->name = $fileName;
                    $uploadData->path = $uploadPath;
                    $uploadData->created = date("Y-m-d H:i:s");
                    $uploadData->modified = date("Y-m-d H:i:s");

                    $data = array(
                        'GalleryID' => $gallery_id,
                        'DateUploaded' => date('Y-m-d h:i:s'),
                        'FileName' => $filenamez
                    );
                    //  print_r($data);
                    $this->GalleryPhoto->set($data);

                    if ($this->GalleryPhoto->save($data)) {
                        $this->Session->setFlash("File has been uploaded and inserted successfully", 'success_message');
                        $this->redirect(array('controller' => 'galleries/gallery_lastpage'));
                    } else {
                        $this->Session->setFlash("Unable to upload file, please try again.", 'error_message');
                    }
                } else {
                    $this->Session->setFlash("Unable to upload file, please try again.", 'error_message');
                }
            } else {
                $this->Session->setFlash("Please choose a file to upload.", 'error_message');
            }
        }
        $this->set('uploadData', $uploadData);



        $photogallerydetails = $this->GalleryPhoto->find('first', array('conditions' => array('GalleryPhoto.PhotoID' => $pid)));

        $galleryid = $photogallerydetails['GalleryPhoto']['GalleryID'];

        $galleryname = $this->Gallery->find('first', array('conditions' => array('Gallery.GalleryID' => $galleryid)));



        $this->set('photogallerydetails', $photogallerydetails);
        $this->set('galleryname', $galleryname);
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
        //  print_r($taskccommentid); die;
        $this->GalleryPhoto->query("DELETE FROM Photos WHERE PhotoID = '" . $pid . "'");
        //$TaskName = $this->Schedule->find('first',array('conditions'=>array('Schedule.TaskID'=>$tid)));
        //print_r($TaskName['Schedule']['Name']);
        $this->Session->setFlash("Gallery Photo has been removed from the project.", 'success_message');
        $this->redirect(array('controller' => 'galleries', 'action' => 'edit_gallery', $gid));
    }

}
