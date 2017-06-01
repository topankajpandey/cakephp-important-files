<?php
App::uses('AppModel', 'Model');
class Permission extends AppModel {

	var $useTable  = 'user_permissions';	
	public $primaryKey = 'id';
	public $actsAs = array('Containable');
	
}
?>

