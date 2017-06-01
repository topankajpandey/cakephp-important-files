<?php

App::uses('Helper', 'View');
App::import("Model", "Project");
App::import("Model", "Forum");
App::import("Model", "Photo");
App::import("Model", "ForumInvite");
App::import("Model", "SettingsGlobal");

class CustomHelper extends Helper {

    public $helpers = array('Html', 'Form', 'Session');

    function setActiveMenu($check_action) {
        $current_action = Inflector::classify($this->params['controller']);
        $setActive = [];

        if ($check_action == $current_action || $check_action == '/' . $current_action) {
            $setActive = ['class' => 'active'];
        } else {
            $setActive = ['class' => ''];
        }
        return $setActive;
    }

    function setActiveMenuAdmin($check_action, $extra = NULL) {

        if ($extra) {
            $action = $this->params['action'];
        } else {
            $action = Inflector::classify($this->params['controller']);
        }

        if ($check_action == $action) {
            echo 'active';
        } else {
            echo NULL;
        }
    }

    function project_member($memberArr = []) {
        if (!empty($memberArr)) {
            foreach ($memberArr as $key => $member) {
                if ($key == 0) {
                    $member = $member['User']['FirstName'];
                } else {
                    $member = ", " . $member['User']['FirstName'];
                }
                echo ucfirst($member);
            }
        }
    }

    function checked_permission_list($list = [], $action_value) {
        if (!empty($list)) {
            $explodePermissionList = explode(",", $list['Permission']['actions']);
            if (in_array($action_value, $explodePermissionList)) {
                echo "checked";
            }
        }
    }

    function check_authority($action_value) {
        $index = $this->Session->read('user_session_data');
        if (!empty($index)) {
            $memberId = $this->Session->read('Auth.User.MemberID');
            if ($memberId == 100)
                return true;
            $explodePermissionList = explode(",", $index['actions']);
            if (in_array($action_value, $explodePermissionList)) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return false;
    }

    public function get_user_profile($fieldValue, $additionalTagStart = False, $additionalTagEnd = False, $width = false, $class = false, $height = false) {
        $html = $additionalTagStart;
        $path = 'files/member/' . $fieldValue;
        if ($width) {
            $new_width = $width;
        } else {
            $new_width = 50;
        }
        if (isset($fieldValue) && !empty($fieldValue) && file_exists($path)) {
            $html .= '<img class="' . $class . '" src="' . $this->webroot . 'files/member/' . $fieldValue . '" width="' . $new_width . '" height="' . $height . '">';
        } else {
            $html .= '<img class="' . $class . '" src="' . $this->webroot . 'img/dummy_person.png" width="' . $new_width . '">';
        }
        $html .= $additionalTagEnd;
        return $html;
    }
 
    public function get_site_logo($type=false, $additionalTagStart = False, $additionalTagEnd = False, $width = false, $class = false, $height = false) {
        $model = new SettingsGlobal();
        $data = $model->find('first', array('fields' => ['site_logo', 'site_title', 'logo_status']));
        $html = "";
        if (!empty($data)) {
           
            if ($type == 'site_title') {
                return $data['SettingsGlobal']['site_title'];
            }else{
                $logo = $data['SettingsGlobal']['site_logo'];
                $html .= $additionalTagStart;
                $path = 'files/settings/logo/' . $logo;
                if ($width) {
                    $new_width = $width;
                } else {
                    $new_width = 50;
                }
                $html = '<a class="pull-left logo" href="'.$this->webroot.'">';
                if (isset($logo) && !empty($logo) && file_exists($path)) {
                    $html .= '<img class="' . $class . '" src="' . $this->webroot . $path. '" width="' . $new_width . '" height="' . $height . '">';
                } else {
                    $html .= '<img src="' . $this->webroot . 'img/images/logo.png" alt="logo">';
                }
                $html .='</a>';
                $html .= $additionalTagEnd;
            }
        }
        return $html;
    }

    public function total_thread($thread, $params) {
        $model = new Forum();
        if ($params == 'total_thread') {
            $count = $model->find('count', array('conditions' => array('Forum.Thread' => $thread)));
            return '<strong>' . $count . '</strong> Thread';
        } else {
            $getArr = $model->query("SELECT DISTINCT `PostedBy` FROM Forum WHERE Thread = '$thread'");
            $count = count($getArr);
            return '<strong>' . $count . '</strong> User';
        }
    }

    public function clearProjectSortLink() {
        if (!empty($this->Session->read('Userdefined'))) {
            $link = $this->webroot . 'documents/clear_sorting_project';
            echo '<a class="edit documents" href="' . $link . '">Clear Sorting</a>';
        }
    }

    /* public function check_count_exist_member_in_forum($forum_id, $member_id) {
      $model = new ForumInvite();
      $conditions = array('ForumInvite.ForumID' => $forum_id, 'ForumInvite.user_id' => $member_id);
      $count =  $model->find('count', $conditions);
      echo $forum_id.' : '.$member_id;
      return $count;
      } */
}
