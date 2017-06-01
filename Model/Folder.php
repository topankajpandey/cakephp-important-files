<?php

App::uses('AppModel', 'Model');

class Folder extends AppModel {

    var $useTable = 'Folders';
    public $primaryKey = 'FolderID';
    public $actsAs = array('Containable');
    public $hasMany = array(
        'Documents' => array(
            'className' => 'Document',
            'foreignKey' => 'FolderID'
        ),
        'Forum' => array(
            'className' => 'Forum',
            'foreignKey' => 'ProjectID',
            'conditions' => array('Forum.type' => 'documents')
        )
    );
    
}

?>