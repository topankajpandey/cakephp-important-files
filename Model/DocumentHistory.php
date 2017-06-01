<?php
App::uses('AppModel', 'Model');
class DocumentHistory extends AppModel {
    
    public $useTable = 'DocumentHistory';
	public $actsAs = array('Containable');
	
}
?>