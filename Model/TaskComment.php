<?php
App::uses('AppModel', 'Model');
class TaskComment extends AppModel {

	var $useTable  = 'TaskComments';
	public $primaryKey = 'TaskCommentID';
	public $actsAs = array('Containable');
	
	
	
}