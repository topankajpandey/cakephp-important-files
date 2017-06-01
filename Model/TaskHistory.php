<?php
App::uses('AppModel', 'Model');
class TaskHistory extends AppModel {

	var $useTable  = 'TaskHistory';
	public $primaryKey = 'TaskHistID';
	public $actsAs = array('Containable');
	
	
	
}