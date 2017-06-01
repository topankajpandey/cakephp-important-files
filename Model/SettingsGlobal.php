<?php
App::uses('AppModel', 'Model');
class SettingsGlobal extends AppModel {
	var $useTable  = 'Settings_Global';	
        
        var $validate = array(
        'site_title' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter site title'
            )
        ),
        'logo_status' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Please select logo or text'
            )
        ),
        'site_logo' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Please choose the file'
            )
        )
    );
}
?>