<?php
App::uses('AppModel', 'Model');
class Member extends AppModel {

	var $useTable  = 'Members';
	public $primaryKey = 'MemberID';
	public $actsAs = array('Containable');
	
	
}