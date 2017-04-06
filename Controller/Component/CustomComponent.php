<?php

App::uses('Component', 'Controller');
App::import("Model", "Project");
App::import("Model", "Folder");
App::import("Model", "Document");
App::import("Model", "ProjectFeed");
App::import("Model", "User");
App::import("Model", "ProjectMember");

class CustomComponent extends Component {

    public $components = array('Session', 'Auth', 'RequestHandler');

    public function get_members($type, $condition = [], $field = [], $recursive = NULL, $limit = NULL, $offset = NULL) {
        $model = new User();
        $members = $model->find($type, array('recursive' => $recursive, 'conditions' => $condition, 'fields' => $field, 'order' => 'User.MemberID DESC', 'limit' => $limit, 'offset' => $offset));
        return $members;
    }

    public function getExistMemberInProject($memberId) {
        $model = new ProjectMember();
        $userData = $model->find('all', array('conditions' => array('ProjectMember.MemberID' => $memberId), 'fields' => array('ProjectMember.ProjectID')));
        $PID = array();
        $projectLists = array();
        foreach ($userData as $pmembers) {
            $PID[] = $pmembers['ProjectMember']['ProjectID'];
        }
        return $PID;
    }

    public function get_projects($type, $ProjectID = NULL, $field = [], $recursive = NULL, $limit = NULL) {
        $model = new Project();
        $memberId = $this->Session->read('Auth.User.MemberID');
        $PID = $this->getExistMemberInProject($memberId);
        $condition = ['Project.Deleted' => 0, 'Project.Archived' => 0, 'Project.ProjectID IN' => $PID];
        if ($ProjectID) {
            $condition = ['Project.ProjectID IN' => $PID, 'Project.ProjectID' => $ProjectID, 'Project.Deleted' => 0, 'Project.Archived' => 0];
        }

        $project = $model->find($type, array('recursive' => $recursive, 'conditions' => $condition, 'fields' => $field, 'limit' => $limit));
        return $project;
    }

    public function get_project_feeds($type, $ProjectID = NULL, $recursive = NULL, $limit = NULL, $offset = NULL) {
        $model = new ProjectFeed();
        $condition = [];
        if ($ProjectID) {
            $condition = ['ProjectFeed.ProjectID' => $ProjectID, 'ProjectFeed.FeedVersion' => 2];
        }

        $projectFeed = $model->find($type, array('recursive' => $recursive, 'conditions' => $condition, 'order' => 'ProjectFeed.ProjectFeedID DESC', 'limit' => $limit, 'offset' => $offset));
        return $projectFeed;
    }

    public function get_documents($type, $DocumentID = NULL, $field = [], $recursive = NULL) {
        $model = new Document();
        $condition = [];
        if ($DocumentID) {
            $condition = ['Document.DocumentID' => $DocumentID];
        }

        $document = $model->find($type, array('recursive' => $recursive, 'conditions' => $condition, 'fields' => $field));
        return $document;
    }

    public function get_folders_by_project($type, $field = [], $FolderID = NULL, $recursive = NULL) {
        $model = new Folder();
        $folders = $model->find($type, array('recursive' => $recursive, 'conditions' => array('Folder.ProjectID' => $FolderID), 'fields' => $field));
        return $folders;
    }

    public function get_folders_by_id($type, $field = [], $FolderID = NULL, $recursive = NULL) {
        $model = new Folder();
        $folders = $model->find($type, array('recursive' => $recursive, 'conditions' => array('FolderID' => $FolderID), 'fields' => $field));
        return $folders;
    }

    function curl_connect($url, $postfield = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        if ($postfield) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield);
        }
        $json_return = curl_exec($ch);
        curl_close($ch);
        return json_decode($json_return);
    }

    public function JSONEncode($array, $array_format = true) {
        if ($array_format)
            $array = self::My()->JSONArrayFormat($array);
        return json_encode($array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

    public function send($status_code, $response = []) {
        $ack = 'error';
        $success = ['200', '204'];
        if (in_array($status_code, $success))
            $ack = 'success';
        $json['status_code'] = $status_code;
        $json['ack'] = $ack;
        $json['response'] = $response;
        echo $this->JSONEncode($json, 0);
        die();
    }

    function get_member_name($session) {
        $firstName = (isset($session['FirstName'])) ? $session['FirstName'] : '';
        $lastName = (isset($session['LastName'])) ? $session['LastName'] : '';
        return $firstName . ' ' . $lastName;
    }

    function validate_email($e) {
        return (bool) preg_match("`^[a-z0-9!#$%&'*+\/=?^_\`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_\`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$`i", trim($e));
    }

    function html_header() {
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> <html lang="en"><head> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="format-detection" content="telephone=no"> <title>Single Column</title> <style type="text/css"> body{margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;}table{border-spacing: 0;}table td{border-collapse: collapse;}.ExternalClass{width: 100%;}.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div{line-height: 100%;}.ReadMsgBody{width: 100%; background-color: #ebebeb;}table{mso-table-lspace: 0pt; mso-table-rspace: 0pt;}img{-ms-interpolation-mode: bicubic;}.yshortcuts a{border-bottom: none !important;}@media screen and (max-width: 599px){.force-row, .container{width: 100% !important; max-width: 100% !important;}}@media screen and (max-width: 400px){.container-padding{padding-left: 12px !important; padding-right: 12px !important;}}.ios-footer a{color: #aaaaaa !important; text-decoration: underline;}</style> </head> <body style="margin:0; padding:0;" bgcolor="#F0F0F0" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0"> <table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0"> <tr> <td align="center" valign="top" bgcolor="#F0F0F0" style="background-color: #F0F0F0;"> <br><table border="0" width="600" cellpadding="0" cellspacing="0" class="container" style="width:600px;max-width:600px"> <tr> <td class="container-padding header" align="left" style="font-family:Helvetica, Arial, sans-serif;font-size:24px;font-weight:bold;padding-bottom:12px;color:#DF4726;padding-left:24px;padding-right:24px; background: #069 none repeat scroll 0 0;"> <img src="http://gbc.projectengineer.net/gbc_logo.png" style="width:130px;height:70px"> </td></tr>';
        return $html;
    }

    function email_header() {
        $headers = "From: webmaster@projectengineer.net\r\n" .
                "Reply-To: webmaster@projectengineer.net\r\n" .
                "Return-Path: webmaster@projectengineer.net\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/html; charset=ISO-8859-1\r\n";
        return $headers;
    }

    function html_footer() {
        $html = '<tr><td class="container-padding header" style="background: rgb(0, 102, 153) none repeat scroll 0px 0px; color: rgb(255, 255, 255); font-family: Helvetica,Arial,sans-serif; font-size: 16px; font-weight: bold; padding-top: 8px; padding-bottom: 8px;" align="left"><center>Copyright &copy; Rivergreen Software | Contact Us </center></td></tr>';
        $html .='</table> </td></tr></table></body></html>';
        return $html;
    }

    public function checkSessionProject($project_id, $condition=[]) {
        if (!empty($this->Session->read('Userdefined'))) {
            $session_project_id = $this->Session->read('Userdefined.project_id');
            return $conditions = array('Project.ProjectID' => $project_id);
        }
        return $condition;
    }

}
