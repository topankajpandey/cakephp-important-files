<?php
App::uses('AppModel', 'Model');
class Message extends AppModel {

	var $useTable  = 'Messages';
	public $primaryKey = 'MessageID';
}

