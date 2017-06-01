<?php
App::uses('AppModel', 'Model');
class SettingsProject extends AppModel {

	var $useTable  = 'Settings_Project';	
	public $primaryKey = 'PreferencesID';
	public $actsAs = array('Containable');
	
}
?>