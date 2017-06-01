<?php
App::uses('AppModel', 'Model');
class DocumentOwner extends AppModel {
    
    public $useTable = 'DocumentOwners';
	public $actsAs = array('Containable');
	
}
?>

