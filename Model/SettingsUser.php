<?php
App::uses('AppModel', 'Model');
class SettingsUser extends AppModel {

	var $useTable  = 'Settings_User';	
	public $primaryKey = 'PreferencesID';
	public $actsAs = array('Containable');
	
}
?>