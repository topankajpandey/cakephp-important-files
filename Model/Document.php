<?php
App::uses('AppModel', 'Model');
class Document extends AppModel {
    
    var $useTable  = 'Documents';
	public $primaryKey = 'DocumentID';
	public $actsAs = array('Containable');
    
    
}

