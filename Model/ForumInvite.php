<?php
App::uses('AppModel', 'Model');
class ForumInvite extends AppModel {

	var $useTable  = 'ForumInvite';	
	public $primaryKey = 'ForumInviteID';
	public $actsAs = array('Containable'); 
}
?>