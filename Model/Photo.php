<?php
App::uses('AppModel', 'Model');
class Photo extends AppModel {
	var $useTable  = 'Photos';
	public $primaryKey = 'PhotoID';
}
