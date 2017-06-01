<?php
App::uses('AppModel', 'Model');
class PhotoGalleryHistory extends AppModel {

	var $useTable  = 'PhotoGalleryHistory';
	public $primaryKey = 'PhotoGalleryHistoryID';
	public $actsAs = array('Containable');
	
}
